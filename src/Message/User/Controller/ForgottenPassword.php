<?php

namespace Message\User\Controller;

use Message\User\User;
use Message\User\Event;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for requesting a password reset link & resetting passwords.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ForgottenPassword extends \Message\Cog\Controller\Controller
{
	/**
	 * Render the password request form.
	 *
	 * @param  string $resetRoute           Name of the route to use for the reset link
	 *                                      emailed to the user
	 * @param  string $email                Default email address to pre-fill the form with
	 *
	 * @return \Message\Cog\HTTP\Response   The response object
	 */
	public function request($resetRoute, $email = null)
	{
		return $this->render('Message:User::password/request', array(
			'form'       => $this->_getForgottenForm($resetRoute)
		));
	}

	/**
	 * Run the password request action, emailing the user a secure & unique link
	 * to use to reset their password.
	 *
	 * @todo implement email component when built
	 * @todo update password requested at timestamp within event listener??
	 *
	 * @return Response           The response object
	 */
	public function requestAction()
	{
		$redirect = $this->redirectToReferer();

		$form = $this->_getForgottenForm();

		// If no form data set on request, redirect the user back to referer
		if (!$form->isValid()) {
			return $redirect;
		}

		$data = $form->getFilteredData();

		// Get the user
		$user = $this->_services['user.loader']->getByEmail($data['email']);

		// Throw error if user is not found
		if (!$user) {
			$this->addFlash('error', sprintf('Could not find user for email address `%s`', $data['email']));
			return $redirect;
			exit;
		}

		// Update the "password requested at" timestamp
		$this->_services['user.edit']->updatePasswordRequestTime($user);

		// Generate the hash
		$hash = $this->_generateHash($user);

		// Email it to the user
		mail(
			$data['email'],
			'Password request token',
			$this->generateUrl($data['reset_route'], array(
				'email' => $data['email'],
				'hash'  => $hash,
			), true)
		);

		// Dispatch password request event
		$this->get('event.dispatcher')->dispatch(
			Event\Event::PASSWORD_REQUEST,
			new Event\Event($user)
		);

		// Give positive feedback
		$this->addFlash('success', sprintf(
			'We\'ve emailed you a secure link to use to reset your password. It will expire in %s.',
			$this->get('cfg')->user->forgottenPasswordExpiry
		));

		// Redirect to referer
		return $redirect;
	}

	/**
	 * Render the password reset form
	 *
	 * @param string $email                 User email address for password reset
	 * @param string $hash                  Hash to determine user is correct and link is not expired
	 * @param string $redirectURL           URL to redirect to once password has been reset
	 *
	 * @return \Message\Cog\HTTP\Response   The response object
	 */
	public function reset($email, $hash, $redirectURL = '/')
	{
		$user = $this->get('user.loader')->getByEmail($email);

		$this->_validateHash($user, $hash, $redirectURL);

		return $this->render('::password/reset', array(
			'form' => $this->_getResetForm($email, $hash, $redirectURL),
		));
	}

	/**
	 * Run the password reset action, resetting the password to user submitted if valid
	 *
	 * @param string $email                             User email address for password reset
	 * @param string $hash                              Hash to determine user is correct and link is not expired,
	 *                                                  taken from email sent to user
	 * @throws AccessDeniedHttpException                Throws exception if user does not exist
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse       Redirect response object
	 */
	public function resetAction($email, $hash)
	{
		$user = $this->get('user.loader')->getByEmail($email);

		// Check user exists
		if (!$user) {
			throw new AccessDeniedHttpException('User not found for this password reset request.');
		}

		$form = $this->_getResetForm($email, $hash);

		// Check form is valid and data can be collection
		if (!$form->isValid() || (!$data = $form->getFilteredData())) {
			return $this->redirectToReferer();
		}

		$this->_validateHash($user, $hash, $data['redirect']);

		// Check passwords match
		if ($data['password'] !== $data['confirm']) {
			$this->addFlash('error', 'Your passwords do not match');
			return $this->redirectToReferer();
		}

		// Change the password & clear the requested timestamp
		$this->get('user.edit')->changePassword($user, $data['password']);
		$this->get('user.edit')->clearPasswordRequestTime($user);

		// Log the user in
		$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);
		$this->get('event.dispatcher')->dispatch(
			Event\Event::LOGIN,
			new Event\Event($user)
		);

		return $this->redirect($data['redirect']);
	}

	/**
	 * Create hash from user password request
	 *
	 * @param User $user        User requesting the password
	 *
	 * @return string           Returns hash
	 */
	protected function _generateHash(User $user)
	{
		$hash = new \Message\Cog\Security\Hash\SHA1($this->_services['security.salt']);

		return $hash->encrypt(
			implode('-', array(
				$user->id,
				$user->passwordRequestAt->getTimestamp(),
			)),
			$this->_services['cfg']->user->forgottenPasswordPepper
		);
	}

	/**
	 * Check that hash is valid and has not timed out
	 *
	 * @param User $user                    User from which hash was generated
	 * @param string $hash                  Hash to validate
	 * @throws AccessDeniedHttpException    Throws exception if hash is not valid, or if the reset link has expired
	 *
	 * @return bool                         Returns true if hash is valid
	 */
	protected function _validateHash(User $user, $hash)
	{
		// Check there is a password requested timestamp & the hash is correct
		if (!$user->passwordRequestAt || $hash !== $this->_generateHash($user)) {
			throw new AccessDeniedHttpException('Hash invalid.');
		}

		$passwordExpiry = $user->passwordRequestAt->add(
			\DateInterval::createFromDateString($this->get('cfg')->user->forgottenPasswordExpiry)
		);

		// Check if password request has expired
		if ($passwordExpiry < new \DateTime) {
			throw new AccessDeniedHttpException('This password reset link has expired.');
		}

		return true;
	}

	/**
	 * Create form for password reset request
	 *
	 * @param string | null $resetRoute         Route to reset
	 *
	 * @return \Message\Cog\Form\Handler        Returns form handler (aka form)
	 */
	protected function _getForgottenForm($resetRoute = null)
	{
		$form = $this->get('form');
		$form->setAction($this->generateUrl('user.password.request.action'))
			->setMethod('post')
			->setName('forgotten')
			->setDefaultValues(array(
				'reset_route' => $resetRoute
			));
		$form->add('email', 'text', 'Email address')
			->val()
			->email();
		$form->add('reset_route', 'hidden');

		return $form;
	}

	/**
	 * Create form for password reset
	 *
	 * @param string $email                 User email for password reset
	 * @param string $hash                  Hash sent to user to validate password reset
	 * @param string | null $redirectURL    URL to redirect upon validation of form
	 *
	 * @return \Message\Cog\Form\Handler    Returns form handler (aka form)
	 */
	protected function _getResetForm($email, $hash, $redirectURL = null)
	{
		$action = $this->generateUrl('user.password.reset.action', array(
			'email' => $email,
			'hash' => $hash,
		));

		$form = $this->get('form');
		$form->setAction($action)
			->setMethod('post')
			->setName('reset')
			->setDefaultValues(array(
				'redirect' => $redirectURL
			));
		$form->add('password', 'password', 'New password');
		$form->add('confirm', 'password', 'Confirm password');
		$form->add('redirect', 'hidden');

		return $form;
	}
}