<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['user']->setPrefix('/user');

		$router['user']->add('user.login.action', '/login', '::Controller:Authentication#loginAction')
			->setMethod('POST');

		$router['user']->add('user.logout', '/logout/{csrfHash}', '::Controller:Authentication#logoutAction')
			->enableCsrf('csrfHash');

		$router['user']->add('user.password.request.action', '/password/request', '::Controller:ForgottenPassword#requestAction')
			->setMethod('POST');

		$router['user']->add('user.password.reset.action', '/password/reset/{email}/{hash}', '::Controller:ForgottenPassword#resetAction')
			->setMethod('POST');
	}
}