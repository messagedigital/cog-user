<?php

namespace Message\User;

/**
 * Interface defining a basic user model.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface UserInterface
{
	/**
	 * Get the user's full name.
	 *
	 * @return string The user's full name
	 */
	public function getName();

	/**
	 * Get user initials
	 *
	 * @return string
	 */
	public function getInitials();
}