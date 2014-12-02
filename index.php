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
		'database' => 'twodolist'
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
// TODO: Call appropriate controllers based on the Request Path
$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

function dumpEntity($entity, $message) {
	echo "<h2>Dumping Entity: " . get_class($entity) . "</h2>$message";
	echo "<ul>";
	foreach ($entity->getFieldValues() as $field => $value) {
		echo "<li>$field: $value</li>";
	}
	echo "</ul>";
}

// Test entity objects
function setupTestData() {
	try {
		// Setup a new user
		$user = new User();
		$user->firstName = 'Mario';
		$user->middleName = 'J.';
		$user->lastName = 'Wunderlich';
		$user->userName = 'mjwunderlich';
		$user->persist();

		dumpEntity($user, "Created user " . $user->userName);

		// Create a new item
		$item = new Item();
		$item->user()->set($user);
		$item->title = 'Item 1';
		$item->brief = 'This is the first item';
		$item->notes = 'These are some notes for the first item';
		$item->content = 'Content data can be used for a number of things';
		$item->persist();

		dumpEntity($item, "Created item " . $item->title);

		// Create Attachments
		// Attachments represent file attachments in the app
		$attachment1 = new Attachment();
		$attachment1->user()->set($user);
		$attachment1->resource = 'https://www.mjwunderlich.com/resource-1.png';
		$attachment1->type = 1;

		dumpEntity($attachment1, "Created attachment " . $attachment1->resource);

		$attachment2 = new Attachment();
		$attachment2->user()->set($user);
		$attachment2->resource = 'Applications/Data/0839827239384-3923-9734-92738472/Documents/resource-2.png';
		$attachment2->type = 0;

		dumpEntity($attachment2, "Created attachment " . $attachment2->resource);

		$attachment3 = new Attachment();
		$attachment3->user()->set($user);
		$attachment3->resource = 'https://www.mjwunderlich.com/resource-3.png';
		$attachment3->type = 1;

		dumpEntity($attachment3, "Created attachment " . $attachment3->resource);

		$attachment4 = new Attachment();
		$attachment4->user()->set($user);
		$attachment4->resource = 'https://www.mjwunderlich.com/resource-4.png';
		$attachment4->type = 1;

		dumpEntity($attachment4, "Created attachment " . $attachment4->resource);

		// Create some comments
		// Coments can have 1 attachment
		$comment1 = new Comment();
		$comment1->user()->set($user);
		$comment1->attachment()->set($attachment1);
		$comment1->comment = 'Funny picture...';

		dumpEntity($comment1, "Created comment " . $comment1->comment);

		$comment2 = new Comment();
		$comment2->user()->set($user);
		$comment2->comment = 'These are my 2 cents.';

		dumpEntity($comment2, "Created comment " . $comment2->comment);

		// Add attachments to item - adding an entity to a relationship like this, persists the entity
		$item->attachments()->add($attachment2);
		$item->attachments()->add($attachment3);
		$item->attachments()->add($attachment4);

		// Add comments to item - adding an entity to a relationship like this, persists the entity
		$item->comments()->add($comment1);
		$item->comments()->add($comment2);

		// Create some child items
		$child1 = new Item();
		$child1->user()->set($user);
		$child1->parent()->set($item);
		$child1->title = 'Child item 1';
		$child1->brief = 'This is the first Child item';
		$child1->notes = 'These are some notes';
		$child1->content = 'Content data can be used for a number of things';

		$child2 = new Item();
		$child2->user()->set($user);
		$child2->parent()->set($item);
		$child2->title = 'Child item 2';
		$child2->brief = 'This is the second Child item';
		$child2->notes = 'These are some notes';
		$child2->content = 'Content data can be used for a number of things';

		// This persists the children
		$item->children()->add($child1);
		$item->children()->add($child2);

		// Add some attachments to child1
		$child1->attachments()->add($attachment1);
		$child1->attachments()->add($attachment2);

		// Add some attachments to child2
		$child2->attachments()->add($attachment2);
		$child2->attachments()->add($attachment3);

		// Test the Children one-to-many relationship
		$children = $item->children()->fetchAll();
		foreach($children as $child) {
			dumpEntity($child, "Got child: " . $child->title);
		}
	}
	catch (Exception $exception) {
		trigger_error( "Error setup up test data: " . $exception->getMessage() );
	}
}

$users = (new User())->fetchAll();
if (empty($users)) {
	setupTestData();
}
else {
	echo "<h1>Hello world!</h1>";
}
?>
