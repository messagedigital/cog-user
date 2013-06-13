<?php

namespace Message\User\EventListener;

use Message\User\AnonymousUser;

use Message\Cog\Event\Event;
use Message\Cog\Event\EventListener;
use Message\Cog\Event\SubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Event listener for permissions related behaviour.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Permissions extends EventListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::REQUEST => array(
				array('checkPermissions')
			),
			'modules.load.success' => array(
				array('registerPermissions', -100)
			),
		);
	}

	/**
	 * Register the permissions to the permissions registry from the group
	 * collection.
	 *
	 * This is run once all modules have been loaded.
	 *
	 * @param Event $event The event
	 */
	public function registerPermissions(Event $event)
	{
		$this->_services['user.permission.registry']->registerGroups(
			$this->_services['user.groups']
		);
	}

	/**
	 * Check if the current user has permission to access this request.
	 *
	 * This asks the permissions registry whether the current request is
	 * protected. A route is protected when any user group registers a
	 * permission for the route itself or a route collection it is within).
	 *
	 * @param GetResponseEvent $event The event instance
	 *
	 * @throws AccessDeniedHttpException If the request is protected and the
	 *                                   current user does not have permission
	 *                                   to access it
	 */
	public function checkPermissions(GetResponseEvent $event)
	{
		// Skip this listener if the request is a sub-request
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return false;
		}

		// Skip if this request isn't protected
		if (!$this->_services['user.permission.registry']->isProtected($this->_services['request'])) {
			return false;
		}

		// Only check permissions if the user is logged in
		if (!($this->_services['user.current'] instanceof AnonymousUser)) {
			$groups = $this->_services['user.group.loader']->getByUser($this->_services['user.current']);

			foreach ($groups as $group) {
				if ($this->_services['user.permission.registry']->canGroupAccess($group, $this->_services['request'])) {
					return true;
				}
			}
		}

		throw new AccessDeniedHttpException('You do not have permission to view this page.');
	}
}