<?php

namespace Message\User;

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

	public $signUpDate;
	public $lastLogin;

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_forename . ($this->_surname ? ' ' . $this->_surname : '');
	}
}