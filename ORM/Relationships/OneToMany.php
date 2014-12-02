<?php

require_once ('Relationship.php');

class OneToMany extends Relationship
{
	protected $many = null;

	protected function load($reload = false) {
		if ($this->many && !$reload) {
			return;
		}

		$source = $this->source;
		$sourceJoinField = $this->sourceField;
		$sourceJoinValue = $source->$sourceJoinField;

		$target = $this->targetInstance();
		$targetJoinField = $this->targetField;

		$filter = array('eq' => array($targetJoinField => $sourceJoinValue));
		$list = $target->fetchAllFiltered($filter);

		if (!$this->many) {
			$this->many = array();
		}

		if ($list) {
			foreach ($list as $entity) {
				array_push($this->many, $entity);
			}
		}
	}

	/**
	 * Adds a new Entity of the -Many side to the -One side.
	 *
	 * @param $child the child entity to add to the -One side
	 *
	 * @throws IllegalOpException if the $child's class is not associated with the Relationship
	 * @throws DBException if there was an error writing to database
	 */
	public function add($child) {
		$this->load();
		if (get_class($child) != $this->target) {
			throw new Exception("Child class(" . get_class($child) . ") does not match Relationship");
		}

		$source = $this->source;
		$sourceJoinField = $this->sourceField;
		$sourceJoinValue = $source->$sourceJoinField;
		$targetJoinField = $this->targetField;
		$child->$targetJoinField = $sourceJoinValue;

		try {
			$child->persist();
			array_push($this->many, $child);
		}
		catch (DBException $exception) {
			trigger_error("Failed to save child entity in OneToMany relationship");
		}
	}

	public function remove($child) {
		throw new MethodNotImplementedException();
	}

	/**
	 * Fetches all items in the relationship.
	 *
	 */
	public function fetchAll() {
		$this->load();
		return $this->many;
	}
}

?>
