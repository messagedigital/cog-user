<?php

namespace Message\User\Controller;

use Message\User\UserInterface;
use Message\User\AnonymousUser;
use Message\User\Event;

use Message\Cog\HTTP\Cookie;

/**
 * Controller for user authentication: logging in & out.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Authentication extends \Message\Cog\Controller\Controller
{
	/**
	 * Render the login form and inject a given redirect URL.
	 *
	 * @param string $redirectURL The URL to redirect to after successful login
	 *
	 * @return Response           The response object
	 */
	public function login($redirectURL = '/', $forgottenPasswordRoute = null)
	{
		return $this->render('::login', array(
			'form'                   => $this->_getLoginForm($redirectURL),
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
		$form = $this->_getLoginForm();

		// If no form data set on request, redirect the user back to referer
		if (!$form->isValid()) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		$data = $form->getFilteredData();

		$redirectURL = $data['redirect'];

		// Send the user away if they are already logged in
		if (!($this->get('user.current') instanceof AnonymousUser)) {
			return $this->redirect($redirectURL);
		}

		// Get the user
		$user = $this->get('user.loader')->getByEmail($data['email']);

		// Fire login attempt event
		$this->get('event.dispatcher')->dispatch(
			Event\Event::LOGIN_ATTEMPT,
			new Event\LoginAttemptEvent($data['email'], $user)
		);

		// Check the user exists and the password is correct
		if (!$user || !$this->get('user.password_hash')->check(
			$data['password'],
			$this->get('user.loader')->getUserPassword($user)
		)) {
			$this->addFlash('error', 'Login details incorrect: please check and try again.');

			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		// Set the user session
		$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);

		// Fire the user login event
		$this->get('event.dispatcher')->dispatch(
			Event\Event::LOGIN,
			new Event\Event($user)
		);

		// If the user selected "keep me logged in", set the user cookie
		if (isset($data['remember']) && 1 == $data['remember']) {
			$this->get('http.cookies')->add(new Cookie(
				$this->get('cfg')->user->cookieName,
				$this->get('user.session_hash')->generate($user),
				new \DateTime('+' . $this->get('cfg')->user->cookieLength)
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
	public function logoutAction($redirectURL = '/')
	{
		$user = $this->get('user.current');

		// If the user is already logged out, send them straight on
		if ($user instanceof AnonymousUser) {
			return $this->redirect($redirectURL);
		}

		// Clear the session
		$this->get('http.session')->remove($this->get('cfg')->user->sessionName);
		// Clear the cookie
		$this->get('http.cookies')->add(new Cookie(
			$this->get('cfg')->user->cookieName,
			null,
			1
		));

		// Fire the user logout event
		$this->get('event.dispatcher')->dispatch(
			Event\Event::LOGOUT,
			new Event\Event($user)
		);

		return $this->redirect($redirectURL);
	}

	protected function _getLoginForm($redirectURL = null)
	{
		$handler = $this->get('form')
			->setName('login')
			->setAction($this->generateUrl('user.login.action'))
			->setMethod('POST')
			->setDefaultValues(array('redirect' => $redirectURL));

		$handler->add('email', 'email')
			->val()->email();
		$handler->add('password', 'password');
		$handler->add('redirect', 'hidden');
		$handler->add('remember', 'checkbox');

		return $handler;
	}
}