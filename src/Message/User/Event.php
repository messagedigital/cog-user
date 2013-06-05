<?php

namespace Message\User;

/**
 * User event.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Event extends \Message\Cog\Event\Event
{
	const LOGIN            = 'user.login';
	const LOGOUT           = 'user.logout';
	const PASSWORD_REQUEST = 'user.password.request';
	const PASSWORD_RESET   = 'user.password.reset';

	const CREATE           = 'user.create';
	const EDIT             = 'user.edit';

	const EMAIL_CONFIRMED  = 'user.email_confirmed';

	protected $_user;

	/**
	 * Constructor.
	 *
	 * @see setUser
	 *
	 * @param User $user User relating to this event
	 */
	public function __construct(User $user)
	{
		$this->setUser($user);
	}

	/**
	 * Set the user relating to this event.
	 *
	 * @param User $user
	 */
	public function setUser(User $user)
	{
		$this->_user = $user;
	}

	/**
	 * Get the user relating to this event.
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->_user;
	}
}