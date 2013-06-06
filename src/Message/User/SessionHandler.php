<?php

namespace Message\User;

/**
 *
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SessionHandler
{
	protected $_services;

	/**
	 * Constructor.
	 *
	 */
	public function __construct(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	// session instance
	// cookiecollection instance
	// event dispatcher instance
	// session hash (or move that into this)
	// cfg: session name
	// cfg: cookie name
	// cfg: cookie length

	public function logIn(User $user, $keepLoggedIn = false)
	{
		$this->get('http.session')->set($this->get('cfg')->user->sessionName, $user);

		if ($keepLoggedIn) {
			$this->get('http.cookies')->add(new Cookie(
				$this->get('cfg')->user->cookieName,
				$this->get('user.session_hash')->generate($user),
				new \DateTime('+' . $this->get('cfg')->user->cookieLength)
			));
		}

		$this->get('event.dispatcher')->dispatch(
			Event::LOGIN,
			new Event($user)
		);
	}

	public function logOut()
	{
		$this->get('http.session')->remove($this->get('cfg')->user->sessionName);
		// Clear the cookie
		$this->get('http.cookies')->add(new Cookie(
			$this->get('cfg')->user->cookieName,
			null,
			1
		));

		// Fire the user logout event
		$this->get('event.dispatcher')->dispatch(
			Event::LOGOUT,
			new Event($user)
		);
	}
}