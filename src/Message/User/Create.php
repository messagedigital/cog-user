<?php 

namespace Message\User\Create;

use Message\User\User;
use Message\User\Loader;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;

/**
 * Decorator for creating new users.
 *
 * @author Ewan Valentine <ewan@message.co.uk>
 *
 */
class Create
{	

	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;

	/**
	 * Constructor.
	 *
	 * @param Loader 				$loader 			User loader
	 * @param DBQuery 				$query 				The database query instance
	 * @param DispatcherInterface	$eventDispatcher 	The event dispatcher
	 *
	 */
	public function __construct(Loader $loader, DBQuery $query,
		DispatcherInterface $eventDispatcher, HashInterface $hash)
	{
		$this->_loader 			= $loader;
		$this->_query			= $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_hash			= $hash;
	}

	/**
	 * Create a user.
	 * 
	 * The user object will be persisted to the database, 
	 * deal with authorship
	 * and trigger the event dispatcher.
	 *
	 * @param User $user 		The user object
	 * @param HashInterface		Instance of the hash component for optional password
	 *
	 * return User
	 */
	public function create(User $user, $password = null)
	{

		$hash->encrypt($password);

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
				password 			= :password?sn
		', array(
			'email'				=> $user->getEmail,
			'email_confirmed' 	=> $user->getEmailConfirmed,
			'title'				=> $user->title,
			'forename'			=> $user->forename,
			'surname'			=> $user->surname,
			'password'			=> $user->password
		));

		$userID = (int) $result->id();

		$event = new Event($userID);

		$this->_eventDispatcher->dispatch(
			$event::CREATE,
			$event
		);

		return $event->getUser();
	}		
}