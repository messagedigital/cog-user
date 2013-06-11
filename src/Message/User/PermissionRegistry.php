<?php

namespace Message\User;

use Message\Cog\Cache\InstanceInterface as CacheInstanceInterface;
use Message\Cog\HTTP\Request;

/**
 * Registry of user group permissions.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PermissionRegistry
{
	protected $_permissions;

	protected $_protectedRoutes = array();
	protected $_protectedRouteCollections = array();

	/**
	 * Check if a given HTTP request is protected.
	 *
	 * A request becomes protected once any group has registered a permission
	 * for the route to the request, or any route collection that the route to
	 * the request belongs to.
	 *
	 * @param  Request $request The request to check
	 *
	 * @return boolean          True if the request is protected
	 */
	public function isProtected(Request $request)
	{
		// If the current route is in a collection
		if ($request->attributes->has('_route_collections')) {
			// Determine the current route collection(s) that are protected
			$currentRouteCollections = array_intersect(
				$this->_protectedRouteCollections,
				$request->attributes->get('_route_collections')
			);

			// If there are any, return true
			if (!empty($currentRouteCollections)) {
				return true;
			}
		}

		// If this request has a route, and that route is protected, return true
		if ($request->attributes->has('_route')
		 && in_array($request->attributes->get('_route'), $this->_protectedRoutes)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if a given group can access a given request.
	 *
	 * @param  Group\GroupInterface $group   The group
	 * @param  Request              $request The request
	 *
	 * @return boolean                       True if the group have ample permission
	 *
	 * @throws \InvalidArgumentException If the given group has not been
	 *                                   registered to this registry instance
	 */
	public function canGroupAccess(Group\GroupInterface $group, Request $request)
	{
		// If the route isn't protected, all can access it!
		if (!$this->isProtected($request)) {
			return true;
		}

		// Throw exception if we don't know about this group
		if (!isset($this->_permissions[$group->getName()])) {
			throw new \InvalidArgumentException(sprintf(
				'Group `%s` has not been registered to the permissions registry.',
				$group->getName()
			));
		}

		$groupPerms = $this->_permissions[$group->getName()];
		$routeName  = $request->attributes->get('_route');

		// Check if the group can access this entire route collection
		foreach ($request->attributes->get('_route_collections') as $collectionName) {
			if ($groupPerms->hasRouteCollection($collectionName)) {
				return true;
			}
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

	/**
	 * Register a collection of groups to this permission registry.
	 *
	 * @param  Group\Collection $collection The group collection to register
	 *
	 * @return PermissionRegistry           Returns $this for chainability
	 */
	public function registerGroups(Group\Collection $collection)
	{
		foreach ($collection as $group) {
			$this->_registerGroup($group);
		}

		return $this;
	}

	/**
	 * Register a single group to this permission registry.
	 *
	 * @param  Group\GroupInterface $group The group to register
	 *
	 * @return PermissionRegistry          Returns $this for chainability
	 */
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