<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router->add('user.login.action', '/user/login', '::Controller:Authentication#loginAction')
			->setMethod('POST');
		$router->add('user.password.request.action', '/user/password/request', '::Controller:ForgottenPassword#requestAction')
			->setMethod('POST');
	}
}