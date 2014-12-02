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

    public function __construct() {
      $this->uuid = uniqid();
    }

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
		  $fields = $this->getFieldNames();
     	$result = array();
     	foreach ($fields as $field) {
     		$result[$field] = $this->$field;
     	}
     	return $result;
     }

     protected function fillFromResultSet($result) {
     	$assoc = $result->fetch_assoc();
     	if (!$assoc) {
     		throw new DBException("Failed to read from resultset");
     	}

     	$fieldNames = $this->getFieldNames();

 		foreach ($assoc as $column => $value) {
 			$field = MySQL::fieldNameFromColumnName($column);
 			if (!in_array($field, $fieldNames)) {
 				trigger_error("Field($field) for Column($column) not defined for Entity(" . get_class($this) . ")");
 				continue;
 			}

 			if ($value == 'NULL') {
 				$this->$field = null;
 			}
 			else {
 				$this->$field = $value;
 			}
 		}
     }

     /**
      * Fetches a single row identified by the given UUID, and fills the calling object with data from
      * the returned row.
      *
      * @throws ConnectionException if mysql connection cannot be obtained
      * @throws NotFoundException if the requested row is not found
      */
     public function fetchWithUUID($uuid) {
     	try {
	     	$table = $this->getTableName();
	     	$mysql = $this->getMySQL();
	     	$sql = "SELECT * FROM `$table` WHERE `uuid` = '" . $mysql->escape($uuid) . "'";
	     	$result = $mysql->query($sql);
	     	$this->fillFromResultSet($result);

	     	return $this;
	     }
	     catch (NotFoundException $exception) {
	     	throw $exception;
	     }
	     catch (DBException $exception) {
	     	throw $exception;
	     }
     }

     /**
      * Fetches a single row identified by the given ID, and fills the calling object with data from
      * the returned row.
      *
      * @throws ConnectionException if mysql connection cannot be obtained
      * @throws NotFoundException if the requested row is not found
      */
     public function fetchWithID($id) {
     	try {
	     	$table = $this->getTableName();
	     	$mysql = $this->getMySQL();
	     	$sql = "SELECT * FROM `$table` WHERE `id` = " . $mysql->escape($id);
	     	$result = $mysql->query($sql);
	     	$this->fillFromResultSet($result);

	     	return $this;
	     }
	     catch (NotFoundException $exception) {
	     	throw $exception;
	     }
	     catch (DBException $exception) {
	     	throw $exception;
	     }
     }

     /**
      * Fetches all rows, optionally offseted by $start and up to $count rows, and fills a list of entity objects
      * using data from each returned row.
      *
      * @return list of Entity objects, one per row, or FALSE if none are found
      *
      * @throws ConnectionException if mysql connection cannot be obtained
      */
     public function fetchAll($start = 0, $count = 0) {
     	  return $this->fetchAllFiltered(null, 0, 0);
     }

     protected function newInstance() {
        return new static;
     }

     public function fetchAllFiltered($filter, $start = 0, $count = 0) {
     	try {
	     	$table = $this->getTableName();
	     	$mysql = $this->getMySQL();
	     	$sql = "SELECT * FROM `$table`";
	     	if ($filter) {
	     		$sql .= " WHERE " . $this->expandFilter($filter);
	     	}
	     	$result = $mysql->query($sql);
	     	$count = $result->num_rows;

	     	$objects = array();
	     	while ($count--) {
	     		$object = $this->newInstance();
	     		$object->fillFromResultSet($result);
	     		array_push($objects, $object);
	     	}

	     	return $objects;
	     }
	     catch (NotFoundException $exception) {
	     	// Return an empty array
	     	return false;
	     }
	     catch (DBException $exception) {
	     	throw $exception;
	     }

	     // If nothing else...
	     return false;
     }

     protected function expandFilter($filter) {
     	$sql = '';
     	$mysql = $this->getMySQL();
     	foreach ($filter as $key => $array) {

     		foreach ($array as $field => $value) {
     			$sql .= '`' . MySQL::columnNameFromFieldName($field) . '` ';
	     		switch ($key) {
     				case 'lt':
     					$sql .= '<'; break;
     				case 'le':
     					$sql .= '<='; break;
	     			case 'eq':
	     				$sql .= '='; break;
     				case 'ge':
     					$sql .= '>='; break;
     				case 'gt':
     					$sql .= '>'; break;
     				case 'ne':
     					$sql .= '<>'; break;
     				case 'nl':
     					$sql .= 'IS NULL'; break;
     				case 'nn':
     					$sql .= 'IS NOT NULL'; break;
	     		}

	     		if ($key == 'nl' || $key == 'nn') {
	     			continue;
	     		}

	     		$sql .= ' \'' . $mysql->escape($value) . '\'';
	     	}
     	}

     	return $sql;
     }

     /**
      * Inserts or updates the appropriate row in the table.
      *
      */
     public function persist() {
     	$table = $this->getTableName();
     	$mysql = $this->getMySQL();

     	try {
	     	if (isset($this->id) && !is_nan($this->id) && $this->id > 0) {
	     		// Because we already have an id, we know this row exists.
	     		$this->updatedAt = time();
	     		$mysql->updateRow($table, $this->getFieldValues());
	     	}
	     	else {
	     		if (!$this->uuid) {
	     			$this->uuid = uniqid();
	     		}
	     		$this->createdAt = time();
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
      * @return name of the connection.
      */
     public function getConnectionName() {
     	return 'mysql-1';
     }

     /**
      * Returns a valid MySQL database object.
      *
      */
     public function getMySQL() {
     	if ($this->mysql != NULL) {
     		return $this->mysql;
     	}

     	$connection = $this->getConnectionName();

     	// Get a cached instance of a MySQL connection
     	// Or create a new one if not found
     	// $this->mysql = MySQL::getOrCreateMySQLInstance($url, $database, $user, $password);
     	$this->mysql = MySQL::getMySQLInstance($connection);


     	// Return the MySQL connection
     	return $this->mysql;
     }
}

?>
