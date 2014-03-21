<?php

namespace Message\User\Bootstrap;

use Message\User;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['user'] = $services->factory(function() {
			return new User\User;
		});

		// Get the currently logged in user
		$services['user.current'] = $services->factory(function($c) {
			if ($user = $c['http.session']->get($c['cfg']->user->sessionName)) {
				return $user;
			}

			return new User\AnonymousUser;
		});

		$services['user.searcher'] = $services->factory(function($c) {
			return new User\Searcher($c['db.query'], $c['user.loader'], 3);
		});

		$services['user.loader'] = $services->factory(function($c) {
			return new User\Loader($c['db.query']);
		});

		$services['user.edit'] = $services->factory(function($c) {
			return new User\Edit(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.password_hash'],
				$c['user.current'],
				$c['user.groups']
			);
		});

		$services['user.create'] = $services->factory(function($c) {
			return new User\Create(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.password_hash'],
				$c['user.current']
			);
		});

		$services['user.password_hash'] = function($c) {
			return new \Message\Cog\Security\Hash\Bcrypt($c['security.string-generator']);
		};

		$services['user.session_hash'] = function($c) {
			return new User\SessionHash(
				$c['security.hash'],
				$c['user.loader'],
				'aKDx213BZ8X25j8az34TRx'
			);
		};

		$services['user.groups'] = function() {
			return new User\Group\Collection;
		};

		$services['user.group.loader'] = $services->factory(function($c) {
			return new User\Group\Loader($c['user.groups'], $c['db.query']);
		});

		$services['user.permission.registry'] = function() {
			return new User\PermissionRegistry;
		};

		$services['user.register.form'] = function($c) {
			return new User\Form\Register($c);
		};

		$services['user.form.simple_search'] = $services->factory(function($c) {
			return new User\Form\SimpleSearch;
		});

		// Add a templating global for the current user
		$services->extend('templating.globals', function($globals) {
			$globals->set('user', function($services) {
				if ($services['user.current'] instanceof User\AnonymousUser) {
					return null;
				}

				return $services['user.current'];
			});

			return $globals;
		});
	}
}