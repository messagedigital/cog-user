<?php

namespace Message\User\Form;

use Message\Cog\Form\Handler;
use Message\Cog\Service\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Message\User\UserInterface;

class Register extends Handler
{

	public function buildForm($action, $redirectURL, $titles = array(), $data = array(), $translator)
	{
		$data += array('redirect' => $redirectURL);

		$this
			->setName('register')
			->setAction($action)
			->setMethod('POST')
			->setDefaultValues($data);

		$this->add('title', 'choice', $translator->trans('cog.user.user.title'), array(
			'choices' => $titles
		));

		$this->add('forename', 'text', $translator->trans('cog.user.user.firstname'));
		$this->add('surname', 'text', $translator->trans('cog.user.user.lastname'));
		$this->add('email', 'email', $translator->trans('cog.user.user.email'));
		$this->add('password', 'password', $translator->trans('cog.user.user.password.password'));
		$this->add('password_conf', 'password', $translator->trans('cog.user.user.password.confirm'));

		return $this;
	}
}