<?php

namespace Message\User\Controller;

use Message\User\UserInterface;
use Message\User\Event;

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
	 * Render the login form and inject a given redirect URL.
	 *
	 * @param string $redirectURL The URL to redirect to after successful login
	 *
	 * @return Response           The response object
	 */
	public function login($redirectURL = '/', $forgottenPasswordRoute = null)
	{
		// Render the login form
		return $this->render('::login', array(
			'redirectURL'            => $redirectURL,
			'forgottenPasswordRoute' => $forgottenPasswordRoute,
		));
	}

	/**
	 * Run the log in action.
	 *
	 * This checks the credentials entered into the form against the database,
	 * and if they are correct, logs the user in by setting their session. If
	 * the user selected "keep me logged in", the cookie is set.
	 *
	 * @return Response The response object
	 */
	public function loginAction()
	{
		// If no form data set on request, redirect the user back to referer
		if (!$data = $this->_services['request']->request->get('login')) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		$redirectURL = $data['redirect'];

		// Send the user away if they are already logged in
		if ($this->get('user.current') instanceof UserInterface) {
			return $this->redirect($redirectURL);
		}

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

		// Fire the user login event
		$this->_services['event.dispatcher']->dispatch(
			Event::LOGIN,
			new Event($user)
		);

		// If the user selected "keep me logged in", set the user cookie
		if (isset($data['remember']) && 1 == $data['remember']) {
			$this->_services['http.cookies']->add(new Cookie(
				$this->_services['cfg']->user->cookieName,
				$this->_services['user.session_hash']->generate($user),
				new \DateTime('+' . $this->_services['cfg']->user->cookieLength)
			));
		}

		return $this->redirect($redirectURL);
	}

	/**
	 * Logs the currently logged in user out, clearing their session and cookie
	 * (if set).
	 *
	 * @param string $redirectURL The URL to redirect to after successful login
	 *
	 * @return Response The response object
	 */
	public function logout($redirectURL = '/')
	{
		// Clear the session
		$this->_services['http.session']->remove($this->_services['cfg']->user->sessionName);
		// Clear the cookie
		$this->_services['http.cookies']->add(new Cookie(
			$this->_services['cfg']->user->cookieName,
			null,
			1
		));

		// Fire the user logout event
		$this->_services['event.dispatcher']->dispatch(
			Event::LOGOUT,
			new Event($user)
		);

		return $this->redirect($redirectURL);
	}
}