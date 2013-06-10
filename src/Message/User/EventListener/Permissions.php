<?php

namespace Message\User\EventListener;

use Message\Cog\HTTP\Cookie;
use Message\Cog\Event\EventListener;
use Message\Cog\Event\SubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Event listener for restoring a user's session.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Permissions extends EventListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(KernelEvents::REQUEST => array(
			array('checkPermissions', 900)
		));
	}

	/**
	 * Restore the user's session from the "keep me logged in" cookie, if it is
	 * set.
	 *
	 * If the cookie hash is invalid, the cookie will be deleted.
	 *
	 * @param  GetResponseEvent $event The event instance
	 *
	 * @return boolean          True if the user was logged in, false otherwise
	 */
	public function checkPermissions(GetResponseEvent $event)
	{
		$currentRoute = $event->getRequest()->attributes->get('_route');

		// If this route is not protected, we can skip this method
		if (!$this->_services['user.permissions']->isRouteProtected($currentRoute)) {
			return true;
		}

		$groups = $this->_services['user.group.loader']->getByUser($this->_services['user.current']);

		foreach ($groups as $group) {
			if ($this->_services['user.permissions']->canGroupAccess($group, $currentRoute)) {
				return true;
			}
		}

		// todo: change this to the correct exception type
		throw new \Exception('NO YOU CAN\'T');
	}
}