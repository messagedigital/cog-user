<?php

namespace Message\User;

use Message\Cog\Cache\InstanceInterface as CacheInstanceInterface;
use Message\Cog\HTTP\Request;

class PermissionRegistry
{
	protected $_permissions;

	protected $_protectedRoutes = array();
	protected $_protectedRouteCollections = array();

	public function registerGroups(Group\Collection $collection)
	{
		foreach ($collection as $group) {
			$this->_registerGroup($group);
		}

		return $this;
	}

	public function isProtected(Request $request)
	{
		// TODO: check the request actually has a route and collection set?
		// TODO: check route collection
		if (in_array($request->attributes->get('_route'), $this->_protectedRoutes)) {
			return true;
		}

		return false;
	}

	public function canGroupAccess(Group\GroupInterface $group, Request $request)
	{
		// If the route isn't protected, all can access it!
		if (!$this->isProtected($request)) {
			return true;
		}

		if (!isset($this->_permissions[$group->getName()])) {
			throw new \InvalidArgumentException(sprintf(
				'Group `%s` has not been registered to the permissions registry.',
				$group->getName()
			));
		}

		$groupPerms = $this->_permissions[$group->getName()];
		$routeName  = $request->attributes->get('_route');

		// Check if the group can access this entire route collection
		if ($groupPerms->hasRouteCollection($request->attributes->get('_routeCollection'))) {
			return true;
		}

		// Check if the group can access this specific route
		if ($groupPerms->hasRoute($routeName)) {
			if (!$requiredParams = $groupPerms->getRouteRequirements($routeName)) {
				return true;
			}

			// If there are parameter requirements set on the route, check them
			foreach ($requiredParams as $param => $value) {
				if ($value != $request->attributes->get($param)) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	protected function _registerGroup(Group\GroupInterface $group)
	{
		$permissions = new Group\Permissions($group);
		$permissions->run();

		$this->_protectedRoutes = array_unique(array_merge(
			$this->_protectedRoutes,
			$permissions->getRoutes()
		));

		$this->_protectedRouteCollections = array_unique(array_merge(
			$this->_protectedRouteCollections,
			$permissions->getRouteCollections()
		));

		$this->_permissions[$group->getName()] = $permissions;

		return $this;
	}
}