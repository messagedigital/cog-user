<?php

namespace Message\User;

/**
 * Anonymous user object. Represents a user that is not logged in.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class AnonymousUser implements UserInterface
{
	/**
	 * Magic setter. This always throws an exception because properties should
	 * not be set on an anonymous user.
	 *
	 * @param string $name  Property name
	 * @param string $value Property value
	 *
	 * @throws \LogicException Always
	 */
	public function __set($name, $value)
	{
		throw new \LogicException('Properties cannot be set on anonymous users.');
	}

	/**
	 * Magic getter. This always returns null because an anonymous user has no
	 * properties.
	 *
	 * This might seem pointless, but it's here to stop notice errors thrown
	 * when something tries to access a property on the AnonymousUser.
	 *
	 * @param string $name Property name
	 *
	 * @return null
	 */
	public function __get($name)
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'Anonymous User';
	}
}