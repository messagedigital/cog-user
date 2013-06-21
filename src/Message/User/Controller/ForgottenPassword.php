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
	 * @param  string $resetRoute Name of the route to use for the reset link
	 *                            emailed to the user
	 * @param  string $email      Default email address to pre-fill the form with
	 *
	 * @return Response           The response object
	 */
	public function request($resetRoute, $email = null)
	{
		return $this->render('::password/request', array(
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
		$redirect = $this->redirect($this->get('request')->headers->get('referer'));

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
			exit;
			return $redirect;
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

	public function reset($email, $hash, $redirectURL = '/')
	{
		$user = $this->get('user.loader')->getByEmail($email);

		$this->_validateHash($user, $hash, $redirectURL);

		return $this->render('::password/reset', array(
			'form' => $this->_getResetForm($email, $hash, $redirectURL),
		));
	}

	public function resetAction($email, $hash)
	{
		$user = $this->get('user.loader')->getByEmail($email);

		$redirect = $this->redirect($this->get('request')->headers->get('referer'));

		$form = $this->_getResetForm($email, $hash);

		if (!$form->isValid()) {
			return $redirect;
		}

		// If no form data set on request, redirect the user back to referer
		if (!$data = $form->getFilteredData()) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		$this->_validateHash($user, $hash, $data['redirect']);

		// Check user exists
		if (!$user) {
			throw new AccessDeniedHttpException('User not found for this password reset request.');
		}

		// Check the passwords match
		if ($data['password'] !== $data['password_confirm']) {
			$this->addFlash('error', 'The entered passwords do not match: please try again.');

			return $this->redirect($data['redirect']);
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

	protected function _getForgottenForm($resetRoute = null)
	{
		$form = $this->get('form.handler');
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

	protected function _getResetForm($email, $hash, $redirectURL = null)
	{
		$action = $this->generateUrl('user.password.reset.action', array(
			'email' => $email,
			'hash' => $hash,
		));

		$passwordMatch = function($var, $data) {
			return ($var == $data['password']) ? true : false;
		};

		$form = $this->get('form.handler');
		$form->setAction($action)
			->setMethod('post')
			->setName('reset')
			->setDefaultValues(array(
				'redirect' => $redirectURL
			));
		$form->add('password', 'password', 'New password');
		$form->add('password_confirm', 'password', 'Confirm new password')
			->val()
			->rule($passwordMatch)
			->error("'%s' must match 'Password'");
		$form->add('redirect', 'hidden');

		return $form;
	}
}