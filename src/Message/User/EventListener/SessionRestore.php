<?php

namespace Message\User\EventListener;

use Message\Cog\HTTP\Cookie;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Event listener for restoring a user's session.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SessionRestore implements SubscriberInterface, ContainerAwareInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array(KernelEvents::REQUEST => array(
			array('restoreSessionFromCookie')
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	/**
	 * Restore the user's session from the "keep me logged in" cookie, if it is
	 * set.
	 *
	 * If the cookie hash is invalid, the cookie will be deleted.
	 *
	 * @param  GetResponseEvent $event The event instance
	 *
	 * @return boolean          True if the user was logged in, false otherwise
	 */
	public function restoreSessionFromCookie(GetResponseEvent $event)
	{
		// Skip this if there is already a user logged in
		if ($this->_services['user.current']) {
			return false;
		}

		if ($cookie = $event->getRequest()->cookies->get($this->_services['cfg']->user->cookieName)) {
			$user = $this->_services['user.session_hash']->getUserFromHash($cookie);

			// If the hash is invalid, clear the cookie
			if (!$user) {
				$this->_services['http.cookies']->add(new Cookie(
					$this->_services['cfg']->user->cookieName,
					null,
					1
				));

				return false;
			}

			// Othrwise, set the user session
			$this->_services['http.session']->set($this->_services['cfg']->user->sessionName, $user);

			return true;
		}
	}
}