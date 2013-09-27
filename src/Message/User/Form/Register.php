<?php

namespace Message\User\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Message\User\UserInterface;

class Register extends Handler
{

	public function buildForm($action, $redirectURL, $titles = array())
	{
		$this
			->setName('register')
			->setAction($action)
			->setMethod('POST')
			->setDefaultValues(array('redirect' => $redirectURL));
		$this->add('title', 'choice', 'Title', array(
			'choices' => $titles
		));
		$this->add('forename', 'text', 'Forename');
		$this->add('surname', 'text', 'Surname');
		$this->add('email', 'email', 'Email');
		$this->add('password', 'password', 'Password');
		$this->add('password_conf', 'password', 'Confirm password');

		return $this;
	}
}