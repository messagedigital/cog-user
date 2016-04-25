<?php

namespace Message\User;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Security\Hash\HashInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

use DateTime;
use InvalidArgumentException;

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
	protected $_groups;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param HashInterface       $hash            Hash to use for user passwords
	 * @param User                $user            The currently logged in user
	 */
	public function __construct(DBQuery $query, DispatcherInterface $eventDispatcher,
		HashInterface $hash, UserInterface $user, Group\Collection $groups)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_passwordHash    = $hash;
		$this->_currentUser     = $user;
		$this->_groups          = $groups;
	}

	/**
	 * Change the user details for a given user.
	 *
	 * @param  User   $user        	The user to change the details for
	 * @param  string $newTitle
	 * @param  string $newForename
	 * @param  string $newSurname
	 * @param  string $newEmail
	 *
	 * @return User                The user that was updated
	 *
	 * @author Eleanor Shakeshaft
	 */
	public function save(User $user)
	{
		$user->authorship->update(new DateTimeImmutable, $this->_currentUser->id);

		$result = $this->_query->run('
			UPDATE
				user
			SET
				title 	   = :title?s,
				forename   = :forename?s,
				surname    = :surname?s,
				email      = :email?s,
				updated_at = :updatedAt?d,
				updated_by = :updatedBy?in
			WHERE
				user_id = :userID?i
		', array(
			'userID'	=> $user->id,
			'title' 	=> $user->title,
			'forename'	=> $user->forename,
			'surname'	=> $user->surname,
			'email'		=> $user->email,
			'updatedAt'	=> $user->authorship->updatedAt(),
			'updatedBy'	=> $user->authorship->updatedBy(),
		));

		$event = new Event\Event($user);

		return $event->getUser();
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
				updated_at = :updatedAt?d,
				updated_by = :updatedBy?in
			WHERE
				user_id = :userID?i
		', array(
			'userID'    => $user->id,
			'password'  => $hashedPassword,
			'updatedAt' => $user->authorship->updatedAt(),
			'updatedBy' => $user->authorship->updatedBy(),
		));

		$event = new Event\Event($user);

		$this->_eventDispatcher->dispatch(
			Event\Event::PASSWORD_RESET,
			$event
		);

		return $event->getUser();
	}

	/**
	 * Add a user to a group.
	 *
	 * @param User                $user
	 * @param Group\GroupInterface $group
	 *
	 * @return bool
	 */
	public function addToGroup(User $user, Group\GroupInterface $group)
	{
		// Check the user has an id
		if (! $user->id) {
			throw new InvalidArgumentException('User id %s is not valid', $user->id);
		}

		$this->_query->run('
			REPLACE INTO
				user_group
			SET
				user_id = ?i,
				group_name = ?s
		', array(
			$user->id,
			$group->getName(),
		));
	}

	/**
	 * Remove a user from a group.
	 *
	 * @param  User                $user
	 * @param  GroupGroupInterface $group
	 *
	 * @return bool
	 */
	public function removeFromGroup(User $user, Group\GroupInterface $group)
	{
		// Check the user has an id
		if (! $user->id) {
			throw new InvalidArgumentException('User id %s is not valid', $user->id);
		}

		$this->_query->run('
			DELETE FROM
				user_group
			WHERE
				user_id = ?i,
				group_name = ?s
		', array(
			$user->id,
			$group->getName()
		));
	}

	/**
	 * Set the user's groups.
	 *
	 * @param User  $user
	 * @param array $groups
	 *
	 * @return bool
	 */
	public function setGroups(User $user, array $groups)
	{
		// Check the user has an id
		if (!$user->id) {
			throw new InvalidArgumentException('Cannot edit a user with no ID');
		}

		$groupObjects = [];

		foreach ($groups as $group) {
			$group = is_string($group) ? $this->_groups->get($group) : $group;
			if (!$group instanceof Group\GroupInterface) {
				throw new \LogicException('All groups in array should be a group name or an instance of `GroupInterface`');
			}

			$groupObjects[$group->getName()] = $group;
		}

		$groups = $groupObjects;

		// Remove user from all groups
		$this->_query->run('
			DELETE FROM
				user_group
			WHERE
				user_id = ?i
		', $user->id);

		if (empty($groups)) {
			return true;
		}

		// Add user to chosen groups
		$insertQuery = '';
		$insertValues = array();
		foreach ($groups as $group) {
			$insertQuery .= '(?i, ?s),';
			$insertValues[] = $user->id;
			$insertValues[] = $group->getName();
		}
		$insertQuery = substr($insertQuery, 0, -1);
		$this->_query->run('
			INSERT INTO
				user_group (`user_id`, `group_name`)
			VALUES
				' . $insertQuery . '
		', $insertValues);

		return true;
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