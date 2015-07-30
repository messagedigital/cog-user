<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1436193244_AddDescriptionAndJobFields extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				`user`
			ADD COLUMN
				`description` TEXT DEFAULT NULL
			AFTER
				`surname`
		");

		$this->run("
			ALTER TABLE
				`user`
			ADD COLUMN
				`job_title` VARCHAR(255) DEFAULT NULL
			AFTER
				`description`
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				`user`
			DROP
				`description`
		");

		$this->run("
			ALTER TABLE
				`user`
			DROP
				`job_title`
		");
	}
}