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

		// Get the currently logged in user
		$services['user.current'] = function($c) {
			return $c['http.session']->get($c['cfg']->user->sessionName);
		};

		$services['user.loader'] = $services->share(function($c) {
			return new \Message\User\Loader($c['db.query']);
		});

		$services['user.create'] = function($c) {
			return new \Message\User\Create(
				$c['user.current'],
				$c['user.loader'],
				$c['db.query'],
				$c['event.dispatcher']
				// $c['user.password_hash'] Is this needed at this level?
			);
		}

		$services['user.edit'] = function($c) {
			return new \Message\User\Edit(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.password_hash'],
				$c['user.current']
			);
		};

		$services['user.password_hash'] = $services->share(function($c) {
			return new \Message\Cog\Security\Hash\Bcrypt($c['security.salt']);
		});

		$services['user.session_hash'] = $services->share(function($c) {
			return new \Message\User\SessionHash(
				$c['security.hash'],
				$c['user.loader'],
				'aKDx213BZ8X25j8az34TRx'
			);
		});

		$services['user.groups'] = $services->share(function() {
			return new \Message\User\Group\Collection;
		});

		$services['user.group.loader'] = $services->share(function($c) {
			return new \Message\User\Group\Loader($c['user.groups'], $c['db.query']);
		});
	}
}