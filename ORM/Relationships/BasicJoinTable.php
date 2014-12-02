<?php

class BasicJoinTable extends Entity
{
	private $tableName;
	private $data = array();
	private $uninitialized;

	public function __construct($tableName) {
		parent::__construct();
		$this->tableName = $tableName;
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function getFieldNames() {
		$result = parent::getFieldNames();
		foreach ($this->data as $field => $value) {
			array_push($result, $field);
		}
		return $result;
	}

	public function getRelationships() {
		return array();
	}

	public function newInstance() {
		$instance = new BasicJoinTable($this->tableName);
		foreach ($this->data as $field => $value) {
			$instance->$field = null;
		}

		return $instance;
	}

	function __set($name, $value) {
		$this->data[$name] = $value;
	}

	function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}

		return $this->uninitialized;
	}
}

?>