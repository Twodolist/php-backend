<?php

class User extends Entity
{
	public $firstName;
	public $middleName;
	public $lastName;
	public $userName;

	// Instead of storing the password, we're storing an MD5 hash of the password
	public $passwordSalt;

	private $collaborations = null;

	public function collaborations() {
		if (!$this->collaborations) {
			$this->collaborations = new OneToMany($this, 'uuid', 'ollaborator', 'userId');
		}
		return $this->collaborations;
	}

	public function getTableName() {
		return 'users';
	}
}

?>
