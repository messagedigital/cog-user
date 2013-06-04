<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router->add('user.login', '/login', '::Controller:Authentication#login')
			#->setAccess('internal')
			->setOptional('_test')
			;
	}
}