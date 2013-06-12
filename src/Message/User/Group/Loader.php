<?php

namespace Message\User\Group;

use Message\User\UserInterface;

use Message\Cog\DB\Query;

/**
 * Group loader decorator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_query;
	protected $_groups;

	/**
	 * Constructor.
	 *
	 * @param Collection $groups The group collection
	 * @param Query      $query  The database query instance to use
	 */
	public function __construct(Collection $groups, Query $query)
	{
		$this->_groups = $group;
		$this->_query  = $query;
	}

	/**
	 * Load group(s) by name.
	 *
	 * @see _load
	 *
	 * @param  string|array $name   The group name, or an array of group names
	 *
	 * @return array|GroupInterface The group(s)
	 */
	public function getByName($name)
	{
		if (is_array($name)) {
			$return = array();
			foreach ($name as $groupName) {
				$return[$name] = $this->_load($name);
			}

			return $return;
		}

		return $this->_load($name);
	}

	/**
	 * Get all groups that a given user is in.
	 *
	 * @param  UserInterface $user The user to get the groups for
	 *
	 * @return array               An array of the groups the user is in
	 */
	public function getByUser(UserInterface $user)
	{
		$return = array();
		$result = $this->_db->query('
			SELECT
				group_name
			FROM
				user_group
			WHERE
				user_id = ?i
		', $user->id);

		foreach ($result->flatten('group_name') as $group) {
			$return[$group] = $this->_load($group);
		}

		return $return;
	}

	/**
	 * Load a group by name from the group collection.
	 *
	 * @see Message\User\Group\Collection::get
	 *
	 * @param  string $name   The group name
	 *
	 * @return GroupInterface The group
	 */
	protected function _load($name)
	{
		return $this->_groups->get($name);
	}
}