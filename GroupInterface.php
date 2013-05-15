<?php

namespace Message\User;

interface GroupInterface
{
	public function getName();
	public function getDescription();
	public function getUsers();
}