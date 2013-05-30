<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['user'] = function() {
			return new \Message\User\User;
		};

		$services['user.groups'] = $services->share(function() {
			return new \Message\User\Group\Collection;
		});
	}
}