<?php

namespace Message\User;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Security\Hash\HashInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Decorator class for editing users.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Edit
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_passwordHash;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param HashInterface       $hash            Hash to use for user passwords
	 * @param User                $user            The currently logged in user
	 */
	public function __construct(DBQuery $query, DispatcherInterface $eventDispatcher,
		HashInterface $hash, UserInterface $user)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_passwordHash    = $hash;
		$this->_currentUser     = $user;
	}

	public function save(User $user)
	{

	}

	/**
	 * Change the password for a given user.
	 *
	 * @param  User   $user        The user to change the password for
	 * @param  string $newPassword The password in plain text
	 *
	 * @return User                The user that was updated
	 */
	public function changePassword(User $user, $newPassword)
	{
		$hashedPassword = $this->_passwordHash->encrypt($newPassword);

		$user->authorship->update(new DateTimeImmutable, $this->_currentUser->id);

		$result = $this->_query->run('
			UPDATE
				user
			SET
				password   = :password?s,
				updated_at = :updatedAt?i,
				updated_by = :updatedBy?in
			WHERE
				user_id = :userID?i
		', array(
			'userID'    => $user->id,
			'password'  => $hashedPassword,
			'updatedAt' => $user->authorship->updatedAt()->getTimestamp(),
			'updatedBy' => $user->authorship->updatedBy(),
		));

		$event = new Event\Event($user);

		$this->_eventDispatcher->dispatch(
			Event\Event::PASSWORD_RESET,
			$event
		);

		return $event->getUser();
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
	public function updateLastLoginTime(User $user, DateTimeImmutable $time = null)
	{
		if (!$time) {
			$time = new DateTimeImmutable;
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
	public function updatePasswordRequestTime(User $user, DateTimeImmutable $time = null)
	{
		if (!$time) {
			$time = new DateTimeImmutable;
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

	/**
	 * Clear the "password requested at" timestamp for a given user.
	 *
	 * This will invalidate any outstanding password reset links for the user.
	 * It is often called once the password has been successfully reset.
	 *
	 * @param  User   $user The user to update
	 *
	 * @return bool         True if the update was successful
	 */
	public function clearPasswordRequestTime(User $user)
	{
		$result = $this->_query->run('
			UPDATE
				user
			SET
				password_request_at = NULL
			WHERE
				user_id = ?i
		', $user->id);

		$user->passwordRequestAt = null;

		return (bool) $result->affected();
	}
}