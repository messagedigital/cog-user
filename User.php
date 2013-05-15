<?php

namespace Message\User;

class User implements UserInterface
{
	protected $_forename;
	protected $_surname;
	protected $_email;

	public function __construct()
	{

	}

	public function load($data = array())
	{
		foreach ($data as $property => $value) {
			$property = '_'.$property;
			if (isset($this->{$property})) {
				$this->{$property} = $value;
			}
		}
	}

	public function getForename()
	{
		return $this->_forname;
	}

	public function getSurname()
	{
		return $this->_surname;
	}

	public function getEmail()
	{
		return $this->_email;
	}
	
	public function getGroups()
	{
		/* This does nothing yet */
	}
}