<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['user']->setPrefix('/user');

		$router['user']->add('user.login.action', '/login', 'Message:User::Controller:Authentication#loginAction')
			->setMethod('POST');

		$router['user']->add('user.logout', '/logout/{csrfHash}', 'Message:User::Controller:Authentication#logoutAction')
			->enableCsrf('csrfHash');

		$router['user']->add('user.password.request.action', '/password/request', 'Message:User::Controller:ForgottenPassword#requestAction')
			->setMethod('POST');

		$router['user']->add('user.password.reset.action', '/password/reset/{email}/{hash}', 'Message:User::Controller:ForgottenPassword#resetAction')
			->setMethod('POST');
	}
}