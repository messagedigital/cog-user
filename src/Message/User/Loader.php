<?php

namespace Message\User;

use Message\Cog\DB\Query as DBQuery;

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
	 * @param  string|array $email Email address, or an array of email addresses
	 *
	 * @return User|array          The user(s)
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

	}
}