<?php

class Comment extends Entity
{
	public $userId;
	public $attachmentId;
	public $comment;

	public function getTableName() {
		return 'comments';
	}

	protected $user = null;
	public function user() {
		if (!$this->user) {
			$this->user = new OneToOne($this, 'userId', 'User', 'uuid');
		}
		return $this->user;
	}

	protected $attachment = null;
	public function attachment() {
		if (!$this->attachment) {
			$this->attachment = new OneToOne($this, 'attachmentId', 'Attachment', 'uuid');
		}
		return $this->attachment;
	}
}

?>