<?php

namespace Message\User;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Decorator class for deleting users.
 *
 * @author Grace Cooper <grace@message.co.uk>
 */
class Delete
{
	private $_transaction;
	private $_transOverride = false;
	private $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DB\Transaction $transaction
	 * @param DB\Query             $query           The database query instance to use
	 * @param User                $user            The currently logged in user
	 */
	public function __construct(
		DB\Transaction $transaction,
		UserInterface $user)
	{
		$this->_transaction     = $transaction;
		$this->_currentUser     = $user;
	}

	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_transaction = $transaction;
		$this->_transOverride = true;
	}

	public function delete(User $user)
	{
		$this->_delete($user);
		$this->_commit();
	}

	/**
	 * Set a user to deleted
	 *
	 * @param  User   $user        The user to change the details for
	 *
	 * @author Grace Cooper
	 */
	private function _delete(User $user)
	{
		$user->authorship->delete(null, $this->_currentUser);

		$result = $this->_transaction->add('
			UPDATE
				user
			SET
				deleted_at = :updatedAt?d,
				deleted_by = :updatedBy?in
			WHERE
				user_id = :userID?i
		', [
			'userID'	=> $user->id,
			'updatedAt'	=> $user->authorship->deletedAt(),
			'updatedBy'	=> $user->authorship->deletedBy()->id,
		]);
	}

	private function _commit()
	{
		if (!$this->_transOverride) {
			$this->_transaction->commit();
		}
	}
}
