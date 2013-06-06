<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['user']->add('user.login.action', '/user/login', '::Controller:Authentication#loginAction')
			->setMethod('POST');
		$router['user']->add('user.password.request.action', '/user/password/request', '::Controller:ForgottenPassword#requestAction')
			->setMethod('POST');
	}
}