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
	 * Get a nicely formatted name for this group that can be displayed to the
	 * user, e.g. 'Super Admin'.
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

	/**
	 * Register the permissions for this group.
	 *
	 * @param Permissions $permissions The group permissions instance
	 */
	public function registerPermissions(Permissions $permissions);
}