<?php

class ItemCollaborator extends Entity
{
	public $itemId;
	public $userId;
	public $permissions;
	public $invitedAt;
	public $acceptedAt;

	public function getTableName() {
		return 'item_collaborators';
	}
}

?>
