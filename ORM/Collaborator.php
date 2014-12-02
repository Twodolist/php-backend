<?php

class Collaborator extends Entity
{
	public $itemId;
	public $userId;
	public $permissions;
	public $invitedAt;
	public $acceptedAt;

	public function getTableName() {
		return 'collaborators';
	}

	protected $user = null;
	public function user() {
		if (!$this->user) {
			$this->user = new OneToOne($this, 'userId', 'User', 'uuid');
		}
		return $this->user;
	}

	protected $item = null;
	public function item() {
		if (!$this->item) {
			$this->item = new OneToOne($this, 'itemId', 'Item', 'uuid');
		}
		return $this->item;
	}

    protected function getRelationships() {
    	return array(
    		$this->user(),
    		$this->item()
    		);
    }
}

?>
