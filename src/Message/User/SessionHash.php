<?php

namespace Message\User;

use Message\Cog\Security\Hash\HashInterface;

/**
 * Handles hashes that can be used to persist a particular user, because the key
 * is made up of data about the user that will never change.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SessionHash
{
	protected $_hash;
	protected $_userLoader;
	protected $_pepper;

	/**
	 * Constructor.
	 *
	 * @param HashInterface $hash   The hash algorithm class to use
	 * @param Loader        $loader The user loader
	 * @param string        $pepper The pepper (global salt) to use
	 */
	public function __construct(HashInterface $hash, Loader $loader, $pepper)
	{
		$this->_hash       = $hash;
		$this->_userLoader = $loader;
		$this->_pepper     = $pepper;
	}

	/**
	 * Generate a hash for a given user.
	 *
	 * @param  User   $user The user to get the hash for
	 *
	 * @return string       The hash string
	 */
	public function generate(User $user)
	{
		$string = implode('-', array(
			$user->id,
			$user->authorship->createdAt()->getTimestamp(),
		));

		return $user->id . '|' . $this->_hash->encrypt($string, $this->_pepper);
	}

	/**
	 * Get an instance of `User` for a given hash.
	 *
	 * @param  string $hash The hash string
	 *
	 * @return User|false   The prepared `User` instance, or false if the hash
	 *                      was not valid or the user did not exist
	 */
	public function getUserFromHash($hash)
	{
		list($userID) = explode('|', $hash, 2);

		$user = $this->_userLoader->getByID($userID);

		if ($user && $hash === $this->encrypt($user)) {
			return $user;
		}

		return false;
	}
}