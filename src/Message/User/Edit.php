<?php

namespace Message\User;

/**
 * Decorator class for editing users.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Edit
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param User|null           $user            The currently logged in user
	 */
	public function __construct(DBQuery $query, DispatcherInterface $eventDispatcher, User $user = null)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $user;
	}

	public function save(User $user)
	{

	}

	public function changePassword(User $user, $newPassword)
	{

	}

	public function updateLastLoginTime(User $user, \DateTime $time = null)
	{
		if (!$time) {
			$time = new \DateTime;
		}

		$result = $this->_query->run('
			UPDATE
				user
			SET
				last_login_at = :timestamp?i
			WHERE
				user_id = :userID?i
		', array(
			'timestamp' => $time->getTimestamp(),
			'userID'    => $user->id,
		));

		$user->lastLogin = $time;

		return (bool) $result->affected();
	}
}