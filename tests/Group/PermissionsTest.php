<?php

namespace Message\User\Test\Group;

use Message\User\Group\Permissions;

class PermissionsTest extends \PHPUnit_Framework_TestCase
{
	protected $_group;
	protected $_permissions;

	public function setUp()
	{
		$this->_group = $this->getMock('Message\User\Group\GroupInterface', array(
			'registerPermissions',
			'getName',
			'getDisplayName',
			'getDescription',
		));

		$this->_group
			->expects($this->any())
			->method('getName')
			->will($this->returnValue('test-group'));

		$this->_group
			->expects($this->any())
			->method('getDisplayName')
			->will($this->returnValue('Test Group'));

		$this->_group
			->expects($this->any())
			->method('getDescription')
			->will($this->returnValue('Testing group'));

		$this->_permissions = new Permissions($this->_group);
	}

	public function testRun()
	{
		$this->_group
			->expects($this->exactly(1))
			->method('registerPermissions')
			->with($this->_permissions);

		$this->_permissions->run();
	}

	public function testChainability()
	{
		$this->assertSame($this->_permissions, $this->_permissions->addRoute('my.route'));
		$this->assertSame($this->_permissions, $this->_permissions->addRouteCollection('stuff'));
	}

	public function testSettingGettingRoutes()
	{
		$this->assertEmpty($this->_permissions->getRoutes());
		$this->assertFalse($this->_permissions->hasRoute('my.route'));
		$this->assertFalse($this->_permissions->hasRoute('my.other.route'));
		$this->assertFalse($this->_permissions->getRouteRequirements('my.other.route'));

		$this->_permissions
			->addRoute('my.route')
			->addRoute('my.other.route', array('param' => 'val'));

		$this->assertContains('my.route', $this->_permissions->getRoutes());
		$this->assertContains('my.other.route', $this->_permissions->getRoutes());
		$this->assertTrue($this->_permissions->hasRoute('my.route'));
		$this->assertTrue($this->_permissions->hasRoute('my.other.route'));
		$this->assertSame(array('param' => 'val'), $this->_permissions->getRouteRequirements('my.other.route'));
	}

	public function testSettingGettingRouteCollections()
	{
		$this->assertEmpty($this->_permissions->getRouteCollections());
		$this->assertFalse($this->_permissions->hasRouteCollection('my.collection'));
		$this->assertFalse($this->_permissions->hasRouteCollection('my.stuff'));

		$this->_permissions
			->addRouteCollection('my.collection')
			->addRouteCollection('my.stuff');

		$this->assertContains('my.collection', $this->_permissions->getRouteCollections());
		$this->assertContains('my.stuff', $this->_permissions->getRouteCollections());
		$this->assertTrue($this->_permissions->hasRouteCollection('my.collection'));
		$this->assertTrue($this->_permissions->hasRouteCollection('my.stuff'));
	}
}