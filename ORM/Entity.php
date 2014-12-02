<?php

require_once ('MethodNotImplementedException.php');

/**
 * Entity object
 * Abstract base class for all ORM-DB objects that require db persistence.
 * Properties that should not be written to Database should be non-public.
 *
 **/
class Entity
{
	// For internal use
	public $id;

	// All entities use UUID's
	public $uuid;

	// Updated, Created dates
	public $updatedAt;
	public $createdAt;

	private $mysql = NULL;

	public function getFieldNames() {
		$reflect = new ReflectionClass($this);
     	$props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
     	$result = array();
     	foreach ($props as $property) {
     		array_push($result, $property->name);
     	}
     	return $result;
     }

     public function getFieldNamesAsColumnNames() {
     	$properties = $this->getFieldNames();
     	$result = array();
     	foreach ($properties as $property) {
     		$columnName = MySQL::columnNameFromFieldName($property);
     		array_push($result, $columnName);
     	}
     	return $result;
     }

     public function getFieldValues() {
		$reflect = new ReflectionClass($this);
     	$props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
     	$result = array();
     	foreach ($props as $property) {
     		$result[$property->name] = $property->getValue($this);
     	}
     	return $result;
     }

     protected function fillFromResultSet($result) {
     	$assoc = $result->fetch_assoc();
     	if (!$assoc) {
     		throw new DBException("Failed to read from resultset");
     	}

 		foreach ($assoc as $column => $value) {
 			$field = MySQL::fieldNameFromColumnName($column);
 			// echo "Setting $field = $value<br />";
 			if ($value == 'NULL') {
 				$this->$field = null;
 			}
 			else {
 				$this->$field = $value;
 			}
 		}
     }

     public function fetchWithUUID($uuid) {
     	$table = $this->getTableName();
     	$mysql = $this->getMySQL();
     	$sql = "SELECT * FROM `$table` WHERE `uuid` = " . $mysql->escape($uuid);
     	$result = $mysql->query($sql);
     	$this->fillFromResultSet($result);
     }

     public function fetchWithID($id) {
     	$table = $this->getTableName();
     	$mysql = $this->getMySQL();
     	$sql = "SELECT * FROM `$table` WHERE `id` = " . $mysql->escape($id);
     	$result = $mysql->query($sql);
     	$this->fillFromResultSet($result);
     }

     public function fetchAll($start = 0, $count = 0) {
     	$table = $this->getTableName();
     	$mysql = $this->getMySQL();
     	$sql = "SELECT * FROM `$table`";
     	$result = $mysql->query($sql);
     	$count = $result->num_rows;

     	$objects = array();
     	while ($count--) {
     		$object = new static;
     		$object->fillFromResultSet($result);
     		array_push($objects, $object);
     	}

     	return $objects;
     }

     /**
      * Inserts or updates the appropriate row in the table.
      *
      */
     public function persist() {
     	$table = $this->getTableName();
     	$mysql = $this->getMySQL();

     	try {
	     	if (isset($this->id) && !is_nan($this->id) && $this-id > 0) {
	     		// Because we already have an id, we know this row exists.
	     		$mysql->updateRow($table, $this->getFieldValues());
	     	}
	     	else {
	     		$this->id = $mysql->insertRow($table, $this->getFieldValues());
	     	}
	     }
	     catch (DBExceptoin $exception) {
	     	// Re-throw as an Entity exception
	     	throw $exception;
	     }
     }

     /**
      * Returns the name of the table this entity belongs to. This method MUST be overridden by
      * descendants of Entity.
      *
      * @return name of table.
      *
      * @throws MethodNotImplementedException if the instance's class does not implement this method
      */
     public function getTableName() {
     	throw new MethodNotImplementedException();
     }

     /**
      * Returns the name of the database this entity belongs to. This method can be overridden by
      * descendants of Entity to use a different database. If not overridden, this method returns the
      * standard database, `twodo`.
      *
      * @return name of database.
      */
     public function getDatabaseName() {
     	return 'twodo';
     }

     /**
      * Returns a valid MySQL database object.
      *
      */
     public function getMySQL() {
     	if ($this->mysql != NULL) {
     		return $this->mysql;
     	}

     	$url = 'localhost';
     	$user = 'root';
     	$password = '';
     	$database = $this->getDatabaseName();

     	// Get a cached instance of a MySQL connection
     	// Or create a new one if not found
     	$this->mysql = MySQL::getOrCreateMySQLInstance($url, $database, $user, $password);

     	// Return the MySQL connection
     	return $this->mysql;
     }
}

?>
