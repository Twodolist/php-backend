<?php

require_once ( './DB/ConnectionException.php' );
require_once ( './DB/NotFoundException.php' );
require_once ( './DB/QueryException.php' );
require_once ( './ORM/MethodNotImplementedException.php' );

require_once ( './DB/MySQL.php' );

require_once ( './ORM/Relationships/Relationship.php' );
require_once ( './ORM/Relationships/OneToMany.php' );
require_once ( './ORM/Relationships/OneToOne.php' );

require_once ( './ORM/Entity.php' );
require_once ( './ORM/Item.php' );
require_once ( './ORM/User.php' );
require_once ( './ORM/Collaborator.php' );
require_once ( './ORM/Comment.php' );
require_once ( './ORM/Attachment.php' );

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
$item = (new Item())->fetchWithID(1);
$comment = (new Comment())->fetchWithID(1);
$attachment = (new Attachment())->fetchWithID(1);

echo '<br />';

// $item->comments()->add($comment);
$item->attachments()->add($attachment);

echo '<br />';
echo '<br />';

$itemComments = $item->comments()->fetchAll();
var_dump($itemComments[0]->getFieldValues());

echo '<br />';
echo '<br />';

$itemAttachments = $item->attachments()->fetchAll();
var_dump($itemAttachments[0]->getFieldValues());

?>
