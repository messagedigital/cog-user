<?php

namespace Message\User;

class User implements UserInterface
{
	public $id;
	public $email;
	public $password;
	public $emailConfirmed;
	public $authorship;

	public $title;
	public $forename;
	public $surname;

	public $signUpDate;
	public $lastLogin;

	public function getName()
	{
		return $this->_forename . ($this->_surname ? ' ' . $this->_surname : '');
	}
}