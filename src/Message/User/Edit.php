<?php

namespace Message\User;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\Event\DispatcherInterface;

use DateTime;

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

	public function addToGroup(User $user, Group\GroupInterface $group)
	{

	}

	public function removeFromGroup(User $user, Group\GroupInterface $group)
	{

	}

	/**
	 * Update the "last login at" timestamp for a given user in the database
	 * and the model.
	 *
	 * @param  User          $user The user to update
	 * @param  DateTime|null $time The date & time to set, or null to be generated
	 *
	 * @return bool                True if the update was successful
	 */
	public function updateLastLoginTime(User $user, DateTime $time = null)
	{
		if (!$time) {
			$time = new DateTime;
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

		$user->lastLoginAt = $time;

		return (bool) $result->affected();
	}

	/**
	 * Update the "password requested at" timestamp for a given user in the
	 * database and the model.
	 *
	 * @param  User          $user The user to update
	 * @param  DateTime|null $time The date & time to set, or null to be generated
	 *
	 * @return bool                True if the update was successful
	 */
	public function updatePasswordRequestTime(User $user, DateTime $time = null)
	{
		if (!$time) {
			$time = new DateTime;
		}

		$result = $this->_query->run('
			UPDATE
				user
			SET
				password_request_at = :timestamp?i
			WHERE
				user_id = :userID?i
		', array(
			'timestamp' => $time->getTimestamp(),
			'userID'    => $user->id,
		));

		$user->passwordRequestAt = $time;

		return (bool) $result->affected();
	}
}