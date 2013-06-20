<?php 

namespace Message\User;

use Message\User\User;
use Message\User\Loader;
use Message\User\Event\Event;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Security\Hash\HashInterface;

/**
 * Decorator for creating new users.
 *
 * @author Ewan Valentine <ewan@message.co.uk>
 */
class Create
{	

	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;
	protected $_hash;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param Loader 				$loader 			User loader
	 * @param DBQuery 				$query 				The database query instance
	 * @param DispatcherInterface	$eventDispatcher 	The event dispatcher
	 * @param HashInterface			$hash 				For password hashing
	 * @param UserInterface			$currentUser 		Sets UserInterface object for current user
	 *
	 */
	public function __construct(Loader $loader, DBQuery $query,
		DispatcherInterface $eventDispatcher, 
		HashInterface $hash = null, User $currentUser = null)
	{
		$this->_loader 			= $loader;
		$this->_query			= $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_hash			= $hash;
		$this->_currentUser		= $currentUser;
	}

	/**
	 * Create a user.
	 * w
	 * @param User 			$user 		The user object
	 * @param $password 	Accepts a password as a parameter
	 *
	 * @return User
	 */
	public function create(User $user, $password = null)
	{

		$hashedPassword = $this->_hash->encrypt($password);

		$result = $this->_query->run('
			INSERT INTO
				user
			SET
				created_at 			= UNIX_TIMESTAMP(),
				email 				= :email?s,
				email_confirmed		= :email_confirmed?i,
				title 				= :title?s,
				forename 			= :forename?s,
				surname 			= :surname?s,
				password 			= :password?sn,
				created_by			= :created_by?sn,
				updated_by			= :updated_by?sn,
				updated_at			= :updated_at?sn
		', array(
				'email'				=> $user->email,
				'email_confirmed' 	=> $user->emailConfirmed,
				'title'				=> $user->title,
				'forename'			=> $user->forename,
				'surname'			=> $user->surname,
				'password'			=> $hashedPassword,
				'created_by'		=> $this->_currentUser->id
		));



		$userID = (int) $result->id();
		
		$user = $this->_loader->getByID($userID);

		$event = new Event($user);

		$this->_eventDispatcher->dispatch(
			$event::CREATE,
			$event
		);

		return $event->getUser();
	}		
}