<?php

namespace Message\User\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\User\Task;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		$tasks->add(new Task\CreateAdminUser('user:create-admin', 'Create an admin user via the command line'));
	}
}