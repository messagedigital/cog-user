<?php

namespace Message\User;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\ValueObject\Authorship;

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

	public function getByGroup(Group\GroupInterface $group)
	{

	}

	protected function _load($id)
	{
		$result = $this->_query->run('
			SELECT
				user_id         AS id,
				created_by,
				created_at,
				updated_by,
				updated_at,
				email,
				password,
				email_confirmed AS emailConfirmed,
				title,
				forename,
				surname,
				last_login_at   AS lastLogin
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

		if ($data->lastLogin) {
			$user->lastLogin = new DateTime('@' . $data->lastLogin);
		}

		$user->authorship = new Authorship;
		$user->authorship->create(new DateTime('@' . $data->created_at), $data->created_by);

		if ($data->updated_at) {
			$user->authorship->update(new DateTime('@' . $data->updated_at), $data->updated_by);
		}

		return $user;
	}
}