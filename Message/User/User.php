<?php

namespace Message\User;

class User implements UserInterface
{
	public $id;
	public $forename;
	public $surname;
	public $email;

	protected $_groups = array();

	public function getName()
	{
		return $this->_forename . ' ' . $this->_surname;
	}

	public function getGroups()
	{
		// load groups?

		return $this->_groups;
	}
}