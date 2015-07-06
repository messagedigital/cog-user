<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1436193244_AddDescriptionField extends Migration
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
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				`user`
			DROP
				`description`
		");
	}
}