<?php

namespace Message\User\Group;

class Permissions
{
	protected $_group;

	protected $_routes            = array();
	protected $_routeCollections  = array();
	protected $_routeRequirements = array();

	public function __construct(GroupInterface $group)
	{
		$this->_group = $group;
	}

	public function run()
	{
		$this->_group->registerPermissions($this);
	}

	public function getRoutes()
	{
		return $this->_routes;
	}

	public function getRouteCollections()
	{
		return $this->_routeCollections;
	}

	public function getRouteRequirements($routeName)
	{
		if (!isset($this->_routeRequirements[$routeName])) {
			return false;
		}

		return $this->_routeRequirements[$routeName];
	}

	public function hasRoute($routeName)
	{
		return in_array($routeName, $this->_routes);
	}

	public function hasRouteCollection($collectionName)
	{
		return in_array($collectionName, $this->_routeCollections);
	}

	public function addRoute($routeName, array $paramRequirements = null)
	{
		$this->_routes[$routeName] = $routeName;

		if ($paramRequirements) {
			$this->_routeRequirements[$routeName] = $paramRequirements;
		}

		return $this;
	}

	public function addRouteCollection($collectionName)
	{
		$this->_routeCollections[$collectionName] = $collectionName;

		return $this;
	}
}