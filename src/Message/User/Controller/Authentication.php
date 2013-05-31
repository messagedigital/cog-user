<?php

namespace Message\User\Controller;

use Message\User\UserInterface;

use Message\Cog\HTTP\Cookie;

// add event to check cookie

class Authentication extends \Message\Cog\Controller\Controller
{
	const SESSION_NAME = 'cog-user';
	const COOKIE_NAME  = 'cog-user';

	public function login()
	{
		// Send the user away if they are already logged in
		if ($this->_services['http.session']->get(self::SESSION_NAME) instanceof UserInterface) {
			return $this->redirect('/');
		}
#var_dump($this->_services['user.password_hash']->encrypt('meglily'));
		$this->loginAction();

		return $this->render('::login');
	}

	public function loginAction()
	{
		if ($data = $this->_services['request']->get('login')) {
			$user = $this->_services['user.loader']->getByEmail($data['email']);

			// Check the user exists and the password is correct
			if (!$user || !$this->_services['user.password_hash']->check(
				$data['password'],
				$this->_services['user.loader']->getUserPassword($user)
			)) {
				throw new \Exception('Login details incorrect: please check and try again.');
			}

			$this->_services['http.session']->set(self::SESSION_NAME, $user);
			// update "last login date"
#print_r($data);exit;
			if (isset($data['remember']) && 1 == $data['remember']) {
				$this->_services['http.cookies']->add(new Cookie(
					self::COOKIE_NAME,
					$this->_services['user.session_hash']->generate($user),
					new \DateTime('+' . $this->_services['cfg']->user->cookieLength)
				));
			}

			// where to redirect to now? how do we make it configurable?
		}
	}

	public function logout()
	{
		$this->_services['cache']->delete($this->_services['config.loader']->getCacheKey());
		$this->_services['http.session']->remove(self::SESSION_NAME);

		// where to redirect? how to make this configurable? make it a param?
	}
}