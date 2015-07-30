<?php

namespace Message\User;

use \Message\Cog\ValueObject\Authorship;

/**
 * A simple implementation of a basic user model.
 *
 * Note that the `password` field is not implemented on this model. This is for
 * security reasons. Any operations relating to user passwords should be
 * separate.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class User implements UserInterface
{
	public $id;
	public $email;
	public $emailConfirmed;
	public $authorship;

	public $title;
	public $forename;
	public $surname;
	public $description;
	public $jobTitle;

	public $lastLoginAt;
	public $passwordRequestAt;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->forename . ($this->surname ? ' ' . $this->surname : '');
	}

	/**
	 * Get the user's full name, with their title prepended (if there is one).
	 *
	 * @return string
	 */
	public function getNameWithTitle()
	{
		return ($this->title ? $this->title . ' ' : '') . $this->getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInitials()
	{
		return (substr($this->forename, 0, 1) ?: '') . (substr($this->surname, 0, 1) ?: '');
	}
}