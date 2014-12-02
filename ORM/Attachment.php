<?php

class Attachment extends Entity
{
	public $userId;
	public $resource;
	public $type;

	protected $user = null;
	public function user() {
		if (!$this->user) {
			$this->user = new OneToOne($this, 'userId', 'User', 'uuid');
		}
		return $this->user;
	}

    protected function getRelationships() {
    	return array(
    		$this->user()
    		);
    }

	public function getTableName() {
		return 'attachments';
	}
}

?>
