<?php

namespace Message\User;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\DateTimeImmutable;

use DateTime;

/**
 * User loader decorator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_query;

	/**
	 * Constructor.
	 *
	 * @param DBQuery $query The database query instance to use
	 */
	public function __construct(DBQuery $query)
	{
		$this->_query = $query;
	}

	/**
	 * Load user(s) by their ID.
	 *
	 * @param  int|array $id User ID, or array of user IDs to load
	 *
	 * @return User|array    The user(s)
	 */
	public function getByID($id)
	{
		if (is_array($id)) {
			$return = array();
			foreach ($id as $userID) {
				$return[$userID] = $this->_load($userID);
			}

			return $return;
		}

		return $this->_load($id);
	}

	/**
	 * Load user(s) by their email address.
	 *
	 * If a string is passed as the email address, only one result is returned
	 * as an instance of `User`. If no result was found, `false` is returned.
	 *
	 * If an array of email addresses is passed, an array of results will always
	 * be returned, even if only one result was found. The return array keys
	 * are the email addresses, and the values are the instances of `User`.
	 *
	 * @param  string|array $email Email address, or an array of email addresses
	 *
	 * @return User|array|false    The user(s)
	 */
	public function getByEmail($email)
	{
		$emails = is_array($email) ? $email : array($email);

		$result = $this->_query->run('
			SELECT
				user_id,
				email
			FROM
				user
			WHERE
				email IN (?sj)
		', array($emails));

		$return = array_filter(array_map(
			array($this, '_load'),
			$result->hash('email', 'user_id')
		));

		return is_array($email) ? $return : array_shift($return);
	}

	/**
	 * Get all users in a specific group.
	 *
	 * @param  string|Group\GroupInterface $group Group or group name
	 *
	 * @return array[User]                        Users in the given group
	 */
	public function getByGroup($group)
	{
		if ($group instanceof Group\GroupInterface) {
			$group = $group->getName();
		}

		$result = $this->_query->run('
			SELECT
				user_id
			FROM
				user_group
			WHERE
				group_name = ?s
		', $group);

		return array_filter(array_map(
			array($this, '_load'),
			$result->flatten('user_id')
		));
	}

	public function getBySearchTerm($term)
	{
		$term = str_replace(' ', '', $term);

		$result = $this->_query->run('
			SELECT
				user_id
			FROM
				user
			LEFT JOIN user_address USING (user_id)
			WHERE
				email LIKE :term?s OR
				forename LIKE :term?s OR
				surname LIKE :term?s OR
				CONCAT(forename,surname) LIKE :term?s OR
				replace(user_address.postcode,\' \',\'\') LIKE :term?s
			GROUP BY user_id
		', array(
			'term' => '%' . $term . '%'
		));

		if (! $result or count($result) == 0) {
			return array();
		}

		$return = array_filter(array_map(
			array($this, '_load'),
			$result->flatten('user_id')
		));

		return $return;
	}

	public function getUserPassword(User $user)
	{
		$result = $this->_query->run('
			SELECT
				password
			FROM
				user
			WHERE
				user_id = ?i
		', $user->id);

		return $result->value() ?: false;
	}

	/**
	 * Load a user by their ID.
	 *
	 * @param  int $id        The user ID
	 *
	 * @return User|false     The prepared User instance, or false if the user
	 *                        does not exist
	 */
	protected function _load($id)
	{
		if (!$id) {
			return false;
		}

		$result = $this->_query->run('
			SELECT
				user_id             AS id,
				created_by,
				created_at,
				updated_by,
				updated_at,
				email,
				email_confirmed     AS emailConfirmed,
				title,
				forename,
				surname,
				last_login_at       AS lastLoginAt,
				password_request_at AS passwordRequestAt
			FROM
				user
			WHERE
				user_id = ?i
		', $id);

		if (count($result) != 1) {
			return false;
		}

		$user = new User;
		$data = $result->first();

		$result->bind($user);

		$user->id             = (int) $data->id;
		$user->emailConfirmed = (boolean) $data->emailConfirmed;

		if ($data->lastLoginAt) {
			$user->lastLoginAt = new DateTimeImmutable(date('c', $data->lastLoginAt));
		}

		if ($data->passwordRequestAt) {
			$user->passwordRequestAt = new DateTimeImmutable(date('c', $data->passwordRequestAt));
		}

		$user->authorship = new Authorship;
		$user->authorship->create(new DateTimeImmutable(date('c', $data->created_at)), $data->created_by);

		if ($data->updated_at) {
			$user->authorship->update(new DateTimeImmutable(date('c', $data->updated_at)), $data->updated_by);
		}

		return $user;
	}
}