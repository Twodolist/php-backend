<?php

require_once ('BasicJoinTable.php');

class ManyToMany extends Relationship
{
	protected $joinTable = null;
	protected $joinTableSourceJoin;
	protected $joinTableTargetJoin;
	protected $many = null;

	public function __construct($source, $sourceJoin, $target, $targetJoin, $joinTable, $joinTableSourceJoin, $joinTableTargetJoin) {
		$this->source = $source;
		$this->sourceField = $sourceJoin;
		$this->target = $target;
		$this->targetField = $targetJoin;
		$this->joinTable = $joinTable;
		$this->joinTableSourceJoin = $joinTableSourceJoin;
		$this->joinTableTargetJoin = $joinTableTargetJoin;
	}

	protected function load($reload = false) {
		if ($this->many && !$reload) {
			return;
		}

		if (!$this->many) {
			$this->many = array();
		}

		$source = $this->source;
		$sourceJoinField = $this->sourceField;
		$sourceJoinValue = $source->$sourceJoinField;

		$joinTableSourceJoin = $this->joinTableSourceJoin;
		$joinTableTargetJoin = $this->joinTableTargetJoin;

		// Setup a joinTable to load many-to-many
		$joinTable = new BasicJoinTable($this->joinTable);
		$joinTable->$joinTableSourceJoin = "mario";
		$joinTable->$joinTableTargetJoin = null;

		$filter = array('eq' => array($joinTableSourceJoin => $sourceJoinValue));
		$list = $joinTable->fetchAllFiltered($filter);

		if ($list) {
			foreach ($list as $entity) {
				// Each entity in this list is an instance of JoinTable
				array_push($this->many, $entity);
			}
		}
	}

	public function fetchAll() {
		$this->load();

		// Convert BasicJoinTables into actual Target-type items
		$items = array();
		$target = $this->target;
		$targetJoinField = $this->targetField;
		$joinTableTargetField = $this->joinTableTargetJoin;
		foreach ($this->many as $joinTable) {
			$targetJoinValue = $joinTable->$joinTableTargetField;
			$entity = new $target();

			if ($targetJoinField == 'uuid') {
				$entity->fetchWithUUID($targetJoinValue);
			}
			else if ($targetJoinField == 'id') {
				$entity->fetchWithID($targetJoinValue);
			}
			else {
				// use a filtered query
			}

			array_push($items, $entity);
		}

		return $items;
	}

	/**
	 * Entity must be of the same type as Target.
	 *
	 */
	public function add($entity) {
		$this->load();

		if (get_class($entity) != $this->target) {
			throw new Exception("Entity class(" . get_class($entity) . ") does not match Relationship");
		}

		$source = $this->source;
		$sourceJoinField = $this->sourceField;
		$sourceJoinValue = $source->$sourceJoinField;
		$targetJoinField = $this->targetField;
		$targetJoinValue = $entity->$targetJoinField;

		echo "Target Join Field: $targetJoinField = $targetJoinValue";

		$joinTableSourceField = $this->joinTableSourceJoin;
		$joinTableTargetField = $this->joinTableTargetJoin;

		$joinTable = new BasicJoinTable($this->joinTable);
		$joinTable->$joinTableSourceField = $sourceJoinValue;
		$joinTable->$joinTableTargetField = $targetJoinValue;

		try {
			$source->persist();
			$entity->persist();
			$joinTable->persist();
			array_push($this->many, $joinTable);
		}
		catch (DBException $exception) {
			trigger_error("Failed to save child entity in ManyToMany relationship: " . $exception->getMessage());
		}
	}
}

?>
