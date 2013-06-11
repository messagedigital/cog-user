<?php

namespace Message\User\EventListener;

use Message\Cog\Event\Event;
use Message\Cog\Event\EventListener;
use Message\Cog\Event\SubscriberInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 *
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Permissions extends EventListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::REQUEST => array(
				array('checkPermissions', 31)
			),
			'modules.load.success' => array(
				array('registerPermissions', -100)
			),
		);
	}

	public function registerPermissions(Event $event)
	{
		$this->_services['user.permission.registry']->registerGroups(
			$this->_services['user.groups']
		);
	}

	/**
	 *
	 *
	 * @param  GetResponseEvent $event The event instance
	 *
	 *
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

		$groups = $this->_services['user.group.loader']->getByUser($this->_services['user.current']);

		foreach ($groups as $group) {
			if ($this->_services['user.permission.registry']->canGroupAccess($group, $this->_services['request'])) {
				return true;
			}
		}

		// todo: change this to the correct exception type
		throw new \Exception('NO YOU CAN\'T');
	}
}