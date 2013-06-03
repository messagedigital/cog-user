<?php

namespace Message\User\Controller;

/**
 * Controller for requesting a password reset link & resetting passwords.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ForgottenPassword extends \Message\Cog\Controller\Controller
{
	public function request($prefillEmailAddress = null)
	{

	}

	/**
	 * @todo email the user the link rather than printing it out
	 * @todo implement user feedback properly (negative + positive)
	 */
	public function requestAction()
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

			// Give positive feedback
		}

		// If the POST data was not sent, just render the password request form
		return $this->render('::password/request');
	}

	public function reset($email, $key)
	{

	}

	public function resetAction($email, $key)
	{

	}

	protected function _generateHash(User $user)
	{
		return $c['security.hash']->generate(
			implode('-', array(
				$user->id,
				$user->passwordRequestAt,
			)),
			$c['cfg']->user->forgottenPasswordPepper
		);
	}
}