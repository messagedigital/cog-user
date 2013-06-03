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
	 * {@inheritDoc}
	 *
	 * @todo Don't inject the service container once James' routes branch on Cog
	 * gets merged.
	 */
	public function registerEvents($dispatcher)
	{
		$subscriber = new \Message\User\EventListener\SessionRestore;
		$subscriber->setContainer(\Message\Cog\Service\Container::instance());
		$dispatcher->addSubscriber($subscriber);
	}
}