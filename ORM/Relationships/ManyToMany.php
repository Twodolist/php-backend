<?php

require_once ('BasicJoinTable.php');

class ManyToMany extends Relationship
{
	protected $joinTable = null;
	protected $joinTableSourceJoin;
	protected $joinTableTargetJoin;
	protected $cascadeDelete;
	protected $many = null;

	public function __construct($source, $sourceJoin, $target, $targetJoin, $joinTable, $joinTableSourceJoin, $joinTableTargetJoin, $cascadeDelete = false) {
		$this->source = $source;
		$this->sourceField = $sourceJoin;
		$this->target = $target;
		$this->targetField = $targetJoin;
		$this->joinTable = $joinTable;
		$this->joinTableSourceJoin = $joinTableSourceJoin;
		$this->joinTableTargetJoin = $joinTableTargetJoin;
		$this->cascadeDelete = $cascadeDelete;
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

		$joinTableSourceField = $this->joinTableSourceJoin;
		$joinTableTargetField = $this->joinTableTargetJoin;

		// Setup a joinTable to load many-to-many
		$joinTable = new BasicJoinTable($this->joinTable);
		$joinTable->$joinTableSourceField = null;
		$joinTable->$joinTableTargetField = null;

		$filter = array('eq' => array($joinTableSourceField => $sourceJoinValue));
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
				// TODO: use a filtered query
				throw new MethodNotImplementedException("ManyToMany does not yet support items with join-field = `$targetJoinField`");
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

	protected function getTargetEntityForJoinTable($joinTable) {
		$joinTableTargetField = $this->joinTableTargetJoin;
		$targetJoinField = $this->targetField;
		$targetJoinValue = $joinTable->$joinTableTargetField;

		$target = $this->targetInstance();
		$filter = array('eq' => array($targetJoinField => $targetJoinValue));
		$list = $target->fetchAllFiltered($filter, 0, 1);
		if ($list) {
			return array_shift($list);
		}
		else {
			return false;
		}
	}

	public function delete($entity) {
		// TODO: Perform a reverse-lookup for the appropriate BasicJoinTable object
		// that represents the given $entity
	}

	public function deleteAll() {
		$this->load();
		foreach ($this->many as $joinTable) {
			$joinTable->delete();

			if ($this->cascadeDelete) {
				$target = $this->getTargetEntityForJoinTable($joinTable);
				if ($target) {
					$target->delete();
				}
			}
		}

		$this->many = null;
	}
}

?>
