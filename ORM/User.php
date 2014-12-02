<?php

class User extends Entity
{
	public $firstName;
	public $middleName;
	public $lastName;
	public $userName;

	// Instead of storing the password, we're storing an MD5 hash of the password
	public $passwordSalt;

	public function getTableName() {
		return 'users';
	}
}

?>
