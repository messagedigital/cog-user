<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1463045940_UserDeletion extends Migration
{
	public function up()
	{
		$this->run("
			ALTER table
				`user`
			ADD `deleted_by` INT(11) DEFAULT NULL,
			ADD `deleted_at` INT(11) DEFAULT NULL
		");
	}

	public function down()
	{
		$this->run('
			ALTER table
				`user`
			DROP COLUMN `deleted_by`,
			DROP COLUMN `deleted_at`
		');
	}
}
