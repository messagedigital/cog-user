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

		$services['user.loader'] = $services->share(function($c) {
			return new \Message\User\Loader($c['db.query']);
		});

		$services['user.groups'] = $services->share(function() {
			return new \Message\User\Group\Collection;
		});

		$services['user.group.loader'] = $services->share(function() {
			return new \Message\User\Group\Loader($c['user.groups'], $c['db.query']);
		});

		$services['user.password_hash'] = $services->share(function($c) {
			return new \Message\Cog\Security\Hash\Bcrypt($c['security.salt']);
		});

		$services['user.session_hash'] = $services->share(function($c) {
			return new \Message\User\SessionHash(
				new \Message\Cog\Security\Hash\Bcrypt($c['security.salt']),
				$c['user.loader'],
				'aKDx213BZ8X25j8az34TRx'
			);
		});
	}
}