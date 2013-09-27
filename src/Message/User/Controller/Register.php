<?php

namespace Message\User\Controller;

use Message\User\UserInterface;
use Message\User\AnonymousUser;
use Message\User\Event;

use Message\Cog\HTTP\Cookie;
use Message\Cog\Controller\Controller;

class Register extends Controller
{
	public function index()
	{
		$form = $this->getForm();
	}

	public function getForm()
	{
		$form = $this->get('user.register.form');

		return $form;
	}

}