<?php

require_once ( './DB/MySQL.php' );
require_once ( './ORM/Entity.php' );
require_once ( './ORM/TodoItem.php' );
require_once ( './ORM/User.php' );

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

// Initialize all Database connections

try {
	MySQL::initConnections($connections);
	$connection = MySQL::getMySQLInstance('mysql-1');
	echo 'Got connection: ' . $connection->getUrl() . '/' . $connection->getDatabase() . '<br />';
}
catch (DBException $exception) {
	echo 'Error connecting to database: ' . $exception->getMessage();
	exit(0);
}

// Test the Entity object

/*
$entity = new User();
$entity->uuid = '2';
$entity->firstName = 'Jenny';
$entity->middleName = 'M.';
$entity->lastName = 'Kopp';
$entity->userName = 'kmpp';
$entity->createdAt = 2000;
$entity->updatedAt = 3000;

$entity->persist();
*/

$entity = new User();
$entities = $entity->fetchAll();

echo '<br />';

foreach ($entities as $entity) {
	var_dump($entity->getFieldValues());
	echo '<br />';
	echo '<br />';
}

?>
