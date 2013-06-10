<?php

namespace Message\User;

class Permissions
{

	public function setGroupPermissions(Group\GroupInterface $group)
	{
		$this->_currentGroup = $group->getName();
		$group->setPermissions($this);
		$this->_currentGroup = null;
	}
/*
	->setPermissions(GroupCollection $groups)

	->addRoute($routeName)
	->addRouteCollection($name)
	->addRouteRequirement($route, $param, $value)

	->isRouteProtected($routeName)
	->canAccess($group, $routeName)
*/
}