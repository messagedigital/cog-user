<?php

namespace Message\User;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Security\Hash\HashInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

use DateTime;

/**
 * Decorator class for editing users.
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Create
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_passwordHash;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param HashInterface       $hash            Hash to use for user passwords
	 * @param User                $user            The currently logged in user
	 */
	public function __construct(DB\Query $query, DispatcherInterface $eventDispatcher,
		HashInterface $hash, UserInterface $user)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_passwordHash    = $hash;
		$this->_currentUser     = $user;
	}

	public function save(User $user)
	{
		return $this->create($user);
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(User $user)
	{
		$user->authorship->create(new DateTimeImmutable, $this->_currentUser->id);
		$user->password = $this->_passwordHash->encrypt($user->password);

		$result = $this->_query->run(
			'INSERT INTO
				user
			SET
				email       = :email?s,
				forename    = :forename?s,
				surname     = :surname?s,
				description = :description?sn,
				job_title   = :jobTitle?sn,
				title       = :title?s,
				password    = :password?s,
				created_by  = :created_by?i,
				created_at  = :created_at?i
			', array(
				'email'       => $user->email,
				'forename'    => $user->forename,
				'surname'     => $user->surname,
				'description' => $user->description,
				'jobTitle'    => $user->jobTitle,
				'title'       => $user->title,
				'password'    => $user->password,
				'created_by'  => $user->authorship->createdBy(),
				'created_at'  => $user->authorship->createdAt(),
			)
		);

		if ($this->_query instanceof DB\Transaction) {
			return $user;
		}

		$user->id = $result->id();

		return $user;
	}
}