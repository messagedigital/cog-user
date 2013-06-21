<?php

namespace Message\User\Group;

/**
 * A container for all user groups available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_groups = array();

	/**
	 * Constructor.
	 *
	 * @param array $groups An array of user groups to add
	 */
	public function __construct(array $groups = array())
	{
		foreach ($groups as $name => $group) {
			$this->add($group);
		}
	}

	/**
	 * Add a user group to this collection.
	 *
	 * @param GroupInterface $group The user group to add
	 *
	 * @return GroupCollection      Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If a group with the same name has
	 *                                   already been set on this collection
	 */
	public function add(GroupInterface $group)
	{
		if (isset($this->_groups[$group->getName()])) {
			throw new \InvalidArgumentException(sprintf('User group `%s` is already defined', $group->getName()));
		}

		$this->_groups[$group->getName()] = $group;

		return $this;
	}

	/**
	 * Get a group set on this collection by name.
	 *
	 * @param  string $name   The group name
	 *
	 * @return GroupInterface The group instance
	 *
	 * @throws \InvalidArgumentException If the group has not been set
	 */
	public function get($name)
	{
		if (!isset($this->_groups[$name])) {
			throw new \InvalidArgumentException(sprintf('Group `%s` not set on collection', $name));
		}

		return $this->_groups[$name];
	}

	/**
	 * Get the number of user groups registered on this collection.
	 *
	 * @return int The number of user groups registered
	 */
	public function count()
	{
		return count($this->_groups);
	}

	/**
	 * Return a flattened array of the groups, where the key is the group name
	 * and the value is the display name.
	 *
	 * This is helpful for use in choice menus.
	 *
	 * @return array The flattened array of groups.
	 */
	public function flatten()
	{
		$return = array();

		foreach ($this->_groups as $group) {
			$return[$group->getName()] = $group->getDisplayName();
		}

		return $return;
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_groups`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_groups);
	}
}