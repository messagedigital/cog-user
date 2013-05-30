<?php

namespace Message\User;

class Loader
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}
}

UserLoader->getByID();
UserLoader->getByEmail();
UserLoader->getUsersInGroup(Group $group);
GroupLoader->getByName();
GroupLoader->getByUser(User $user);

