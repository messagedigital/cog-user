<?php

namespace Message\User\EventListener;

use Message\User\Event\Event;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

/**
 * Event listener for when a user logs in to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Login implements SubscriberInterface, ContainerAwareInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array(Event::LOGIN => array(
			array('updateLastLoginTimestamp')
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	/**
	 * Updates the "last login" timestamp for a user when they have logged in.
	 *
	 * @see Message\User\Edit::updateLastLoginTime
	 *
	 * @param  Event $event The event instance
	 *
	 * @return boolean      Result of setting the "last login" timestamp
	 */
	public function updateLastLoginTimestamp(Event $event)
	{
		return $this->_services['user.edit']->updateLastLoginTime($event->getUser());
	}
}