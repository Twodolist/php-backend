<?php

require_once ( './DB/ConnectionException.php' );
require_once ( './DB/NotFoundException.php' );
require_once ( './DB/QueryException.php' );
require_once ( './ORM/MethodNotImplementedException.php' );

require_once ( './DB/MySQL.php' );

require_once ( './ORM/Relationships/Relationship.php' );
require_once ( './ORM/Relationships/OneToMany.php' );

require_once ( './ORM/Entity.php' );
require_once ( './ORM/Item.php' );
require_once ( './ORM/User.php' );
require_once ( './ORM/ItemCollaborator.php' );

// Setup a few default connections
$connections = array(
	'mysql-1' => array(
		'url' => 'localhost',
		'user' => 'root',
		'password' => '',
		'database' => 'twodo'
		),

	'mysql-2' => array(
		'url' => 'localhost',
		'user' => 'root',
		'password' => '',
		'database' => 'twodo'
		)
	);

try {
	// Initialize all Database connections
	MySQL::initConnections($connections);
	$connection = MySQL::getMySQLInstance('mysql-1');
	echo 'Got connection: ' . $connection->getUrl() . '/' . $connection->getDatabase() . '<br />';
}
catch (DBException $exception) {
	echo 'Error connecting to database: ' . $exception->getMessage();
	exit(0);
}

// Get the path

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

// TODO: Call appropriate controllers based on the Request Path

// Test the Entity object
$user = (new User())->fetchWithID(1);

echo '<br />';

$item = (new Item())->fetchWithID(1);
$collabs = $item->itemCollaborators()->fetchAll();

echo '<br />';
echo '<br />';

foreach ($collabs as $collab) {
	var_dump($collab->getFieldValues());
	echo '<br />';
	echo '<br />';
}

?>
