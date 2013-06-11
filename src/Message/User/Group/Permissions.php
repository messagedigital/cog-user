<?php

namespace Message\User\Group;

/**
 * Group permissions.
 *
 * Determines the permissions for a given group and stores these to be accessed
 * by the permission registry.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Permissions
{
	protected $_group;

	protected $_routes            = array();
	protected $_routeCollections  = array();
	protected $_routeRequirements = array();

	/**
	 * Constructor.
	 *
	 * @param GroupInterface $group The group to get permissions for
	 */
	public function __construct(GroupInterface $group)
	{
		$this->_group = $group;
	}

	/**
	 * Register the permissions to this container.
	 *
	 * @see GroupInterface::registerPermissions
	 */
	public function run()
	{
		$this->_group->registerPermissions($this);
	}

	/**
	 * Get all routes that the group have permission to access.
	 *
	 * @return array Array of allowed routes
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}

	/**
	 * Get all route collections that the group have permission to access.
	 *
	 * @return array Array of allowed route collections
	 */
	public function getRouteCollections()
	{
		return $this->_routeCollections;
	}

	/**
	 * Get route permission requirements set for the group for a given route.
	 *
	 * A route requirement is when the group only have access to a particular
	 * route when it has specific property values set.
	 *
	 * @param  string $routeName Name of the route to get requirements for
	 *
	 * @return array|false       Array of route requirements, or false if none
	 *                           are set for this route
	 */
	public function getRouteRequirements($routeName)
	{
		if (!isset($this->_routeRequirements[$routeName])) {
			return false;
		}

		return $this->_routeRequirements[$routeName];
	}

	/**
	 * Check if the group has defined any permissions for a given route.
	 *
	 * @param  string  $routeName The route name to check for
	 *
	 * @return boolean            True if permissions are defined for this route
	 */
	public function hasRoute($routeName)
	{
		return in_array($routeName, $this->_routes);
	}

	/**
	 * Check if the group has defined any permissions for a given route
	 * collection.
	 *
	 * @param  string  $collectionName The route collection name to check for
	 *
	 * @return boolean                 True if permissions are defined for this
	 *                                 route collection
	 */
	public function hasRouteCollection($collectionName)
	{
		return in_array($collectionName, $this->_routeCollections);
	}

	/**
	 * Give the group permission to access a given route.
	 *
	 * If the second argument is passed, the group is only given permission to
	 * access the route if the request has route properties and values that
	 * match that in the second argument.
	 *
	 * @param string     $routeName         The route name to grant access to
	 * @param array|null $paramRequirements Array of route parameter requirements
	 *
	 * @return Permissions                  Returns $this for chainability
	 */
	public function addRoute($routeName, array $paramRequirements = null)
	{
		$this->_routes[$routeName] = $routeName;

		if ($paramRequirements) {
			$this->_routeRequirements[$routeName] = $paramRequirements;
		}

		return $this;
	}

	/**
	 * Give the group permission to access a given route collection.
	 *
	 * @param string $collectionName The route collection name to grant access to
	 *
	 * @return Permissions           Returns $this for chainability
	 */
	public function addRouteCollection($collectionName)
	{
		$this->_routeCollections[$collectionName] = $collectionName;

		return $this;
	}
}