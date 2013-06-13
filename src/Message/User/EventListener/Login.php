<?php

namespace Message\User\EventListener;

use Message\User\Event\Event;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener;

/**
 * Event listener for when a user logs in to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Login extends EventListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(Event::LOGIN => array(
			array('updateLastLoginTimestamp')
		));
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