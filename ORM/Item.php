<?php

require_once ('Relationships/OneToMany.php');
require_once ('Relationships/ManyToMany.php');

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


	protected $user = null;
	public function user() {
		if (!$this->user) {
			$this->user = new OneToOne($this, 'userId', 'User', 'uuid');
		}
		return $this->user;
	}

	protected $parent = null;
	public function parent() {
		if (!$this->parent) {
			$this->parent = new OneToOne($this, 'parentId', 'Item', 'uuid');
		}
		return $this->parent;
	}

	private $collaborators = null;
	public function collaborators() {
		if (!$this->collaborators) {
			$this->collaborators = new OneToMany($this, 'uuid', 'Collaborator', 'itemId');
		}
		return $this->collaborators;
	}

	private $comments = null;
	public function comments() {
		if (!$this->comments) {
			$this->comments = new ManyToMany($this, 'uuid', 'Comment', 'uuid', 'item_comments', 'itemId', 'commentId');
		}
		return $this->comments;
	}

	private $attachments = null;
	public function attachments() {
		if (!$this->attachments) {
			$this->attachments = new ManyToMany($this, 'uuid', 'Attachment', 'uuid', 'item_attachments', 'itemId', 'attachmentId');
		}
		return $this->attachments;
	}

	public function getTableName() {
		return 'items';
	}
}

?>
