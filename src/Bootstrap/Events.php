<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\EventsInterface;

/**
 * Bootstrap for event listeners in this Cogule.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Events implements EventsInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new \Message\User\EventListener\Login);
		$dispatcher->addSubscriber(new \Message\User\EventListener\SessionRestore);
		$dispatcher->addSubscriber(new \Message\User\EventListener\Permissions);
		$dispatcher->addSubscriber(new \Message\User\EventListener\Report);
	}
}