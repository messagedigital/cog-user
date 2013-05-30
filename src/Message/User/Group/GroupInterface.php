<?php

namespace Message\User\Group;

/**
 * Interface that defines a user group.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface GroupInterface
{
	/**
	 * Get the identifier name for the user group, e.g. 'super-admin'.
	 *
	 * @return string The identifier for the group
	 */
	public function getName();

	/**
	 * Get the nicely formatted display name for the group, e.g. 'Super Admin'.
	 *
	 * @return string The display name
	 */
	public function getDisplayName();

	/**
	 * Get a description for this user group.
	 *
	 * @return string The description
	 */
	public function getDescription();
}