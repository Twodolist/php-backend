<?php

class User extends Entity
{
	public $firstName;
	public $middleName;
	public $lastName;
	public $userName;

	// Instead of storing the password, we're storing an MD5 hash of the password
	public $passwordSalt;

	private $itemCollaborations = null;

	public function itemCollaborations() {
		if (!$this->itemCollaborations) {
			$this->itemCollaborations = new OneToMany('item_collaborations', $this, 'uuid', 'ItemCollaborator', 'userId');
		}
		return $this->itemCollaborations;
	}

	public function getTableName() {
		return 'users';
	}
}

?>
