<?php

require_once ('Relationships/OneToMany.php');

class Item extends Entity
{
	// User this item belongs to
	public $userId;

	// Parent of this item, or NULL if this is a top-level item
	public $parentId;

	// DB fields for TodoItem
	public $title;
	public $brief;
	public $content;
	public $notes;
	public $state;

	private $itemCollaborators = null;

	public function itemCollaborators() {
		if (!$this->itemCollaborators) {
			$this->itemCollaborators = new OneToMany('item_collaborators', $this, 'uuid', 'ItemCollaborator', 'itemId');
		}
		return $this->itemCollaborators;
	}

	public function getTableName() {
		return 'items';
	}
}

?>
