<?php

namespace Message\User\Controller;

use Message\User\UserInterface;

use Message\Cog\HTTP\Cookie;

/**
 * Controller for user authentication: logging in & out.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Authentication extends \Message\Cog\Controller\Controller
{
	const SESSION_NAME = 'cog-user';
	const COOKIE_NAME  = 'cog-user';

	/**
	 * Render the login form & run the log in action if the form is submitted.
	 *
	 * This checks the credentials entered into the form against the database,
	 * and if they are correct, logs the user in by setting their session. If
	 * the user selected "keep me logged in", the cookie is set.
	 *
	 * @return Response The response object
	 */
	public function login($redirectURL) // /
	{
		// Send the user away if they are already logged in
		if ($this->_services['http.session']->get($this->_services['cfg']->user->sessionName) instanceof UserInterface) {
			return $this->redirect('/');
		}

		// If form is submitted
		if ($data = $this->_services['request']->get('login')) {
			// Get the user
			$user = $this->_services['user.loader']->getByEmail($data['email']);

			// Check the user exists and the password is correct
			if (!$user || !$this->_services['user.password_hash']->check(
				$data['password'],
				$this->_services['user.loader']->getUserPassword($user)
			)) {
				throw new \Exception('Login details incorrect: please check and try again.');
			}

			// Set the user session
			$this->_services['http.session']->set($this->_services['cfg']->user->sessionName, $user);

			// Update last login date
			$this->_services['user.edit']->updateLastLoginTime($user);

			// If the user selected "keep me logged in", set the user cookie
			if (isset($data['remember']) && 1 == $data['remember']) {
				$this->_services['http.cookies']->add(new Cookie(
					$this->_services['cfg']->user->cookieName,
					$this->_services['user.session_hash']->generate($user),
					new \DateTime('+' . $this->_services['cfg']->user->cookieLength)
				));
			}

			// where to redirect to now? how do we make it configurable?
		}

		return $this->render('::login');
	}

	/**
	 * Logs the currently logged in user out, clearing their session and cookie
	 * (if set).
	 *
	 * @return Response The response object
	 *
	 * @todo Redirect the user somewhere after a successful log out
	 */
	public function logout()
	{
		// Clear the session
		$this->_services['http.session']->remove($this->_services['cfg']->user->sessionName);
		// Clear the cookie
		$this->_services['http.cookies']->add(new Cookie(
			$this->_services['cfg']->user->cookieName,
			null,
			1
		));

		// where to redirect? how to make this configurable? make it a param?

		return $this->redirect('/login');
	}
}