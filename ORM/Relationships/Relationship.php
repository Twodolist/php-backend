<?php

class Relationship
{
	public $name;
	public $source;
	public $target;
	public $sourceField;
	public $targetField;

	protected function sourceInstance() {
		$class = 'Class.' . get_class($this->source);
		return new $class();
	}

	protected function targetInstance() {
		$class = $this->target;
		return new $class();
	}

	public function __construct($name, $source, $sourceField, $target, $targetField) {
		$this->name = $name;
		$this->source = $source;
		$this->sourceField = $sourceField;
		$this->target = $target;
		$this->targetField = $targetField;
	}
}

?>
