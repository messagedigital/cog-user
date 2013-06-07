<?php

namespace Message\User\Event;

/**
 * Event for when a login attempt is made.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class LoginAttemptEvent extends Event
{
	protected $_email;

	/**
	 * Constructor.
	 *
	 * @param string $email The email address for the login attempt
	 * @param User   $user  User relating to this event
	 */
	public function __construct($email, User $user = null)
	{
		$this->_email = $email;

		if ($user) {
			$this->setUser($user);
		}
	}

	/**
	 * Get the email address for the login attempt.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->_email;
	}
}