<?php

namespace Message\User\Controller;

use Message\User\UserInterface;

class Authentication extends \Message\Cog\Controller\Controller
{
	const SESSION_NAME = 'user';

	public function login()
	{
		// Redirect the user away if they are already logged in
		if ($this->_services['http.session']->get(self::SESSION_NAME) instanceof UserInterface) {
			return $this->redirect('/');
		}

		return $this->render('::login');
	}

	public function loginAction()
	{
		die('hi');
		if ($data = $this->_services['request']->get('login')) {
			// remember me thing
		}
	}

	public function logout()
	{
		$this->_services['http.session']->remove('user');
	}
}