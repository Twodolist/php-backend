<?php

// Require exception objects
require_once ('ConnectionException.php');
require_once ('NotFoundException.php');
require_once ('QueryException.php');

/**
 * MySQL database manager.
 * This object relies on Exceptions to report errors, so make sure that operations are placed withing
 * appropriate try/catch blocks.
 *
 */
class MySQL
{
	private $connection;

	private $name;
	private $url;
	private $database;
	private $user;
	private $password;

	// This stores all available connections so that they may be shared
	private static $instances = array();

	/**
	 * Constructs the MySQL manager and connects to the database.
	 *
	 * @return a new MySQL manager object.
	 *
	 * @throws ConnectionException if the connection fails.
	 * @throws NotFoundException if the User or Database do not exist in db.
	 */
	function __construct($url, $database, $user, $password) {
		$this->name = strtolower($user . '@' . $url . '/' . $database);
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
		$this->password = $password;

		$this->connection = new mysqli($this->url, $this->user, $this->password, $this->database);
		if ($this->connection->connect_error) {
			throw new ConnectionException("MySQL connection failed: URL($this->url); USER($this->user); DB($this->database)");
		}
	}

	/**
	 * This method retuns a valid instance of a MySQL connection that uses the same parameters.
	 * If one is not found, it will be created and registered.
	 *
	 * @return A valid instance of MySQL connection.
	 *
	 * @throws ConnectionException if the connection fails.
	 * @throws NotFoundException if the User or Database do not exist in db.
	 */
	public static function getOrCreateMySQLInstance($url, $database, $user, $password) {
		$key = strtolower($user . '@' . $url . '/' . $database);
		$instance = null;
		if (!isset(self::$instances[$key])) {
			try {
				$instance = new MySQL($url, $database, $user, $password);
				$instance->name = $key;
				self::$instances[$instance->name] = $instance;
			} catch (DBException $exception) {
				throw $exception;
			}
		}
		else {
			$instance = self::$instances[$key];
		}

		return $instance;
	}

	public static function getMySQLInstance($name) {
		if (!isset(self::$instances[$name])) {
			return null;
		}

		return self::$instances[$name];
	}

	/**
	 * Initializes MySQL connections from a connections array.
	 *
	 * @throws ConnectionException if a connection could not be created
	 * @throws NotFoundException if a host or database do not exist
	 */
	public static function initConnections($connectionsArray) {
		foreach ($connectionsArray as $key => $connection) {
			$instance = self::getMySQLInstance($key);
			if (!isset($instance)) {
				$instance = new MySQL($connection['url'], $connection['database'], $connection['user'], $connection['password']);
				$instance->name = $key;
				self::$instances[$key] = $instance;
			}

			return $instance;
		}
	}


	/* ==========================================================================================================================
	 * Accessor METHODS
	 */


	public function getName() {
		return $this->name;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getUrl() {
		return $this->url;
	}

	public function getUser() {
		return $this->user;
	}


	/* ==========================================================================================================================
	 * Table Management METHODS
	 */


	/**
	 * Returns an array of column names for the specified table name.
	 *
	 * @return array of column names.
	 *
	 * @throws QueryException if the query cannot be executed.
	 * @throws NotFoundException if the table is not found.
	 * @throws ConnectionException if the db connection is invalid.
	 */
	public function fetchColumnNamesForTable($tableName) {
		$sql = "DESCRIBE `$tableName`";
		$result = $this->connection->query($sql);
		if ($result === false) {
			throw new QueryException("Failed to execute query($sql): " . $this->connection->error);
		}

		$result->data_seek(0);
		$columns = array();
		while ($row = $result->fetch_assoc()) {
			foreach ($row as $key -> $columnName) {
				array_push($columns, $columnName);
			}
		}

		return $columns;
	}

	/**
	 * Give an associative columns->values array, inserts or updates a row identified by the UUID column,
	 * if present.
	 * 
	 */
	public function insertRow($tableName, $fieldValues) {
		$data = $this->buildInsertQuery($fieldValues);
		$sql = "INSERT INTO `$tableName` $data";
		$insertId = $this->connection->query($sql);
		if (!$insertId) {
			throw new DBException("Failed to insert SQL($sql): Error: " . $this->connection->error);
		}
		return $insertId;
	}

	public function updateRow($tableName, $fieldValues) {

	}

	public function deleteRow($tableName, $uuid) {

	}

	public function query($sql) {
		$result = $this->connection->query($sql);
		if (!$result) {
			throw new DBException("Query failed with SQL($sql); Error: " . $this->connection->error);
		}

		return $result;
	}

	public function lastInsertId() {
		return $this->connection->insert_id;
	}

	public function escape($string) {
		return $this->connection->real_escape_string($string);
	}

	public function buildInsertQuery($fieldValues) {
		$columns = '';
		$values = '';
		foreach ($fieldValues as $field => $value) {
			// Array values are not yet supported
			// In the future, these will be used for 1-many relationships
			if (is_array($value)) {
				continue;
			}

			if (!empty($columns)) {
				$columns .= ', ';
				$values .= ', ';
			}
			$columns .= '`' . self::columnNameFromFieldName($field) . '`';
			if (is_string($value)) {
				// TODO: Sanitize the string
				$values .= '\'' . $value . '\'';
			}
			else if (is_numeric($value)) {
				$values .= $value;
			}
			else if (is_null($value) || is_nan($value) || !isset($value)) {
				$values .= 'NULL';
			}
		}

		return "($columns) VALUES ($values)";
	}

	public function buildUpdateQuery($fieldValues) {
		$sql = '';
		foreach ($fieldValues as $field => $value) {
			// Array values are not yet supported
			// In the future, these will be used for 1-many relationships
			if (is_array($value)) {
				continue;
			}

			if ($field == 'id' || $field == 'uuid') {
				continue;
			}

			if (!empty($sql)) {
				$sql .= ', ';
			}
			$sql .= '`' . self::columnNameFromFieldName($field) . '` = ';
			if (is_string($value)) {
				// TODO: Sanitize the string
				$sql .= '\'' . $value . '\'';
			}
			else if (is_numeric($value)) {
				$sql .= $value;
			}
			else if (is_null($value) || is_nan($value) || !isset($value)) {
				$sql .= 'NULL';
			}
		}

		return $sql;
	}

	public function buildWhereQuery($whereValues) {
		return '';
	}


	/* ==========================================================================================================================
	 * Auxiliary METHODS
	 */


	/**
	 * Converts a column name into a field name (underscores to camel-case).
	 *
	 * @return field name equivalent of the given column name.
	 */
	public static function fieldNameFromColumnName($columnName) {
		$parts = explode("_", $columnName);
		$fieldName = $parts[0];
		for ($i=1; $i<count($parts); ++$i) {
			$fieldName .= ucfirst($parts[$i]);
		}

		return $fieldName;
	}

	/**
	 * Converts a field name into a column name (camel-case to underscores).
	 *
	 * @return column name equivalent of the given field name.
	 */
	public static function columnNameFromFieldName($fieldName) {
		$parts = preg_split("/([A-Z][a-z0-9]+)/", $fieldName, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		$columnName = $parts[0];
		for ($i=1; $i<count($parts); ++$i) {
			$columnName .= "_" . strtolower($parts[$i]);
		}

		return $columnName;
	}
}

?>
