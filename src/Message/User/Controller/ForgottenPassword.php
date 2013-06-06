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
	public function request($email = null)
	{
		return $this->render('::password/request', array(
			'email' => $email,
		));
	}

	/**
	 * @todo implement email component when built
	 * @todo implement user feedback properly (negative + positive)
	 * @todo update password requested at timestamp within event listener??
	 * @todo Pass in the route name for the reset URL to this somehow
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
			$this->generateUrl('user.password.reset', array(
				'email' => $data['email'],
				'hash'  => $hash,
			), true)
		);

		// Give positive feedback

		// Redirect to referer
		return $this->redirect($this->get('request')->headers->get('referer'));
	}

	public function reset($email, $hash)
	{
		return $this->render('::password/reset', array(
			'email' => $email,
		));
	}

	public function resetAction($email, $hash)
	{

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