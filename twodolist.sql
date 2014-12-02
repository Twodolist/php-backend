CREATE DATABASE `twodolist`;
USE `twodolist`;

CREATE TABLE `users` (
  `id` int(11) not null auto_increment,
  `uuid` varchar(36) not null,
  `first_name` varchar(40) not null,
  `last_name` varchar(40) not null,
  `middle_name` varchar(40),
  `user_name` varchar(20) not null,
  `password_salt` varchar(36),
  `created_at` int(20) default 0,
  `updated_at` int(20) default 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_uuid` (`uuid`),
  UNIQUE KEY `u_names` (`first_name`, `last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `items` (
  `id` int(11) not null auto_increment,
  `uuid` varchar(36) not null,
  `user_id` varchar(36) not null,
  `parent_id` varchar(36) default null,
  `title` varchar(40) not null,
  `brief` text,
  `content` text,
  `state` int(11) default 0,
  `notes` text,
  `created_at` int(20) not null,
  `updated_at` int(20),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `items` ADD CONSTRAINT `fk_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `items`(`uuid`);
ALTER TABLE `items` ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`uuid`);

CREATE TABLE `item_collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `item_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '0',
  `invited_at` int(20) DEFAULT '0',
  `accepted_at` int(20) DEFAULT '0',
  `created_at` int(20) NOT NULL DEFAULT '0',
  `updated_at` int(20) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `item_collaborators` ADD UNIQUE KEY `u_item_user_id` (`item_id`,`user_id`);
ALTER TABLE `item_collaborators` ADD CONSTRAINT FOREIGN KEY (`item_id`) REFERENCES `items` (`uuid`);
ALTER TABLE `item_collaborators` ADD CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `users` (`uuid`);


CREATE TABLE `attachments` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uuid` varchar(36) NOT NULL,
`user_id` varchar(36) NOT NULL,
`resource` text NOT NULL,
`type` int(11) NOT NULL DEFAULT 0,
`created_at` int(20) NOT NULL DEFAULT 0,
`updated_at` int(20),
PRIMARY KEY (`id`),
UNIQUE KEY `u_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `attachments` ADD CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `users` (`uuid`);


CREATE TABLE `comments` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uuid` varchar(36) NOT NULL,
`user_id` varchar(36) NOT NULL,
`attachment_id` varchar(36),
`comment` text,
`created_at` int(20) NOT NULL DEFAULT 0,
`updated_at` int(20),
PRIMARY KEY (`id`),
UNIQUE KEY `u_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `comments` ADD CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `users` (`uuid`);
ALTER TABLE `comments` ADD CONSTRAINT FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`uuid`);

CREATE TABLE `item_comments` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uuid` varchar(36) NOT NULL,
`item_id` varchar(36) NOT NULL,
`comment_id` varchar(36) NOT NULL,
`created_at` int(20) NOT NULL DEFAULT 0,
`updated_at` int(20),
PRIMARY KEY (`id`),
UNIQUE KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `item_comments` ADD CONSTRAINT FOREIGN KEY (`item_id`) REFERENCES `items` (`uuid`);
ALTER TABLE `item_comments` ADD CONSTRAINT FOREIGN KEY (`comment_id`) REFERENCES `comments` (`uuid`);


CREATE TABLE `item_attachments` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`uuid` varchar(36) NOT NULL,
`item_id` varchar(36) NOT NULL,
`attachment_id` varchar(36) NOT NULL,
`created_at` int(20) NOT NULL DEFAULT 0,
`updated_at` int(20),
PRIMARY KEY (`id`),
UNIQUE KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `item_attachments` ADD CONSTRAINT FOREIGN KEY (`item_id`) REFERENCES `items` (`uuid`);
ALTER TABLE `item_attachments` ADD CONSTRAINT FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`uuid`);

