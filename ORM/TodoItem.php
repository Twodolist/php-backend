<?php

class TodoItem extends Entity
{
	// DB fields for TodoItem
	public $title;
	public $body;

	// Owner represents the User that created this item
	public $owner;

	public function getTableName() {
		return 'todo_items';
	}
}

?>
