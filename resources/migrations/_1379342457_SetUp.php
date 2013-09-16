<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379342457_SetUp extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE IF NOT EXISTS `user` (
			  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` int(11) unsigned DEFAULT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `email` varchar(255) NOT NULL DEFAULT '',
			  `password` varchar(255) NOT NULL DEFAULT '',
			  `email_confirmed` int(1) unsigned NOT NULL DEFAULT '0',
			  `title` varchar(255) DEFAULT NULL,
			  `forename` varchar(255) DEFAULT NULL,
			  `surname` varchar(255) DEFAULT NULL,
			  `last_login_at` int(11) unsigned DEFAULT NULL,
			  `password_request_at` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`user_id`),
			  UNIQUE KEY `email` (`email`),
			  KEY `created_by` (`created_by`),
			  KEY `updated_by` (`updated_by`),
			  KEY `email_confirmed` (`email_confirmed`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE IF NOT EXISTS `user_group` (
			  `user_id` int(11) unsigned NOT NULL,
			  `group_name` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`user_id`,`group_name`),
			  KEY `user_id` (`user_id`),
			  KEY `group_name` (`group_name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				user
		');
		$this->run('
			DROP TABLE IF EXISTS
				user_group
		');
	}
}
