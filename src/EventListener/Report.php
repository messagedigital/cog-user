<?php

namespace Message\User\EventListener;

use Message\Cog\Event\Event;
use Message\Cog\Event\EventListener;
use Message\Cog\Event\SubscriberInterface;

use Message\Mothership\Report\Event as ReportEvents;


class Report extends EventListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			ReportEvents\Events::REGISTER_REPORTS => [
				'registerReports'
			],
		);
	}

	public function registerReports(ReportEvents\BuildReportCollectionEvent $event)
	{
		foreach ($this->get('user.reports') as $report) {
			$event->registerReport($report);
		}
	}
}