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
	 * @todo implement email component when built
	 * @todo implement user feedback properly (negative + positive)
	 */
	public function request($prefillEmailAddress = null)
	{
		if ($data = $this->_services['request']->get('password_request')) {
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
		}

		// If the POST data was not sent, just render the password request form
		return $this->render('::password/request');
	}

	public function reset($email, $hash)
	{

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