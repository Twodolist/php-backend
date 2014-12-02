<?php

class OneToOne extends Relationship
{
	protected $entity;

	protected function load($reload = false) {
		$source = $this->source;
		$sourceJoinField = $this->sourceField;
		$sourceJoinValue = $source->$sourceJoinField;

		if (is_null($sourceJoinValue) || empty($sourceJoinValue)) {
			$this->entity = null;
		}

		if ($this->entity && !$reload) {
			return;
		}

		$target = $this->targetInstance();
		$targetJoinField = $this->targetField;

		$filter = array('eq' => array($targetJoinField => $sourceJoinValue));
		$list = $target->fetchAllFiltered($filter, 0, 1);

		if ($list && count($list)) {
			$this->entity = array_shift($list);
		}
	}

	public function set($entity) {
		try {
			$source = $this->source;
			$sourceJoinField = $this->sourceField;

			$targetJoinField = $this->targetField;
			$targetJoinValue = $entity->$targetJoinField;

			$source->$sourceJoinField = $targetJoinValue;

			$entity->persist();
			$this->entity = $entity;
		}
		catch (Exception $exception) {
			trigger_error("Failed to save source in OneToOne relationship");
		}
	}

	public function get() {
		$this->load();
		return $this->entity;
	}

	public function fetch() {
		return $this->get();
	}
}

?>
