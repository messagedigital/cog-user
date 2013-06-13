<?php

namespace Message\User\Test;

use Message\User\AnonymousUser;

class AnonymousUserTest extends \PHPUnit_Framework_TestCase
{
	static public function getProperties()
	{
		return array(
			array('id'),
			array('name'),
			array('lastLoginAt'),
			array('someOtherProperty'),
		);
	}

	public function testImplementsInterface()
	{
		$user = new AnonymousUser;

		$this->assertInstanceOf('Message\User\UserInterface', $user);
	}

	/**
	 * @dataProvider      getProperties
	 * @expectedException \LogicException
	 */
	public function testSetThrowsException($property)
	{
		$user = new AnonymousUser;

		$user->$property = 'value';
	}

	/**
	 * @dataProvider getProperties
	 */
	public function testGetReturnsNull($property)
	{
		$user = new AnonymousUser;

		$this->assertNull($user->$property);
	}

	public function testGetName()
	{
		$user = new AnonymousUser;

		$this->assertContains('Anonymous', $user->getName());
	}
}