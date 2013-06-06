<?php

namespace Message\User\Controller;

use Message\User\User;

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
			'resetRoute' => $resetRoute,
			'email'      => $email,
		));
	}

	/**
	 * Run the password request action, emailing the user a secure & unique link
	 * to use to reset their password.
	 *
	 * @todo implement email component when built
	 * @todo implement user feedback properly (negative + positive)
	 * @todo update password requested at timestamp within event listener??
	 *
	 * @return Response           The response object
	 */
	public function requestAction()
	{
		// If no form data set on request, redirect the user back to referer
		if (!$data = $this->_services['request']->request->get('password_request')) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		// Get the user
		$user = $this->_services['user.loader']->getByEmail($data['email']);

		if (!$user) {
			throw new \Exception(sprintf('Could not find user for email address `%s`', $data['email']));
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

		$this->get('event.dispatcher')->dispatch(
			Event::PASSWORD_REQUEST,
			new Event($user)
		);

		// Give positive feedback

		// Redirect to referer
		return $this->redirect($this->get('request')->headers->get('referer'));
	}

	public function reset($email, $hash, $redirectURL = '/')
	{
		return $this->render('::password/reset', array(
			'email'       => $email,
			'hash'        => $hash,
			'redirectURL' => $redirectURL,
		));
	}

	public function resetAction($email, $hash)
	{
		// If no form data set on request, redirect the user back to referer
		if (!$data = $this->_services['request']->request->get('password_reset')) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		// Check user exists
		$user = $this->get('user.loader')->getByEmail($email);
		if (!$user) {
			throw new \Exception(sprintf('No user exists for email address `%s`', $email));
		}

		// Check the passwords match
		if ($data['password'] !== $data['password_confirm']) {
			throw new \Exception('The entered passwords do not match: please try again.');
		}

		// Change the password
		$this->get('user.edit')->changePassword($user, $data['password']);

		$this->get('event.dispatcher')->dispatch(
			Event::PASSWORD_RESET,
			new Event($user)
		);

		// Log the user in
		$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);
		$this->get('event.dispatcher')->dispatch(
			Event::LOGIN,
			new Event($user)
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
}