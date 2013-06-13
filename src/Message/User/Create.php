<?php 

use Message\User\User;
use Message\User\Loader;

use Message\Cog\Event\DispatcherInterface;

/**
 * Decorator for creating new users.
 *
 * @author Ewan Valentine <ewan@message.co.uk>
 *
 */
class Create
{	
	/**
	 * Constructor.
	 *
	 * @param Loader 				$loader 			User loader
	 * @param DBQuery 				$query 				The database query instance
	 * @param DispatcherInterface	$eventDispatcher 	The event dispatcher
	 *
	 */
	public function __construct(Loader $loader, DBQuery $query,
		DispatcherInterface $eventDispatcher)
	{
		$this->_loader 			= $loader;
		$this->_query			= $query;
		$this->_eventDispatcher = $eventDispatcher;
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
	public function create(User $user, HashInterface $password = null)
	{
		$result = $this->_query->run('
			INSERT INTO
				user
			SET
				created_at 			= UNIX_TIMESTAMP(),
				email 				= :email?s,
				email_confirmed		= :email_confirmed?i,
				title 				= :title?s,
				forename 			= :forename?s,
				surname 			= :surname?s
		', array(
			'email'				=> $user->getEmail(),
			'email_confirmed' 	=> $user->getEmail_confirmed(),
			'title'				=> $user->title(),
			'forename'			=> $user->forename(),
			'surname'			=> $user->surname()
		));

		$userID = (int) $result->id();

		$this->_eventDispatcher->dispatch(
			$event::CREATE,
			$event
		);

		return $event->getUser();
	}		
}