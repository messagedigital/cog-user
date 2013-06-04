<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router->add('user.login', '/login', '::Controller:Authentication#login')
			->setAccess('internal')
			->setDefault('redirectURL', '/')
			;
		$router->add('user.password.request', '/password/request', '::Controller:ForgottenPassword#request')
			#->setAccess('internal')
			;
		$router->add('user.password.reset', '/password/reset/{email}/{hash}', '::Controller:ForgottenPassword#reset')
			#->setAccess('internal')
			;
		#$router->add('user.password.reset', '/password/reset/{email}/{hash}', '::Controller:ForgottenPassword#resetAction')
			#->setAccess('internal')
			#->setScheme('POST')
		#	;
	}
}