<?php

class Device extends Entity
{
	// Thid device belongs to a single User
	public var $userId;

	// ID/Reference for this device
	public var $ref;

	// Data for this device
	public var $name;
	public var $model;
	public var $brand;
	public var $make;
	public var $os;

	// Version of the app installed on device
	public var $version;

	// The date the app was last accessed from this device
	public var $accessedAt;
}

?>
