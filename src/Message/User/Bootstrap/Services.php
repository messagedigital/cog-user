<?php

namespace Message\User\Bootstrap;

use Message\User;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['user'] = function() {
			return new User\User;
		};

		// Get the currently logged in user
		$services['user.current'] = function($c) {
			if ($user = $c['http.session']->get($c['cfg']->user->sessionName)) {
				return $user;
			}

			return new User\AnonymousUser;
		};

		$services['user.loader'] = $services->share(function($c) {
			return new User\Loader($c['db.query']);
		});

		$services['user.edit'] = function($c) {
			return new User\Edit(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.password_hash'],
				$c['user.current'],
				$c['user.groups']
			);
		};

		$services['user.create'] = function($c) {
			return new User\Create(
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
			return new User\SessionHash(
				$c['security.hash'],
				$c['user.loader'],
				'aKDx213BZ8X25j8az34TRx'
			);
		});

		$services['user.groups'] = $services->share(function() {
			return new User\Group\Collection;
		});

		$services['user.group.loader'] = function($c) {
			return new User\Group\Loader($c['user.groups'], $c['db.query']);
		};

		$services['user.permission.registry'] = $services->share(function() {
			return new User\PermissionRegistry;
		});

		// Add a templating global for the current user
		$services['templating.globals'] = $services->share($services->extend('templating.globals', function($globals) {
			$globals->set('user', function($services) {
				if ($services['user.current'] instanceof User\AnonymousUser) {
					return null;
				}

				return $services['user.current'];
			});

			return $globals;
		}));
	}
}