<?php 

use \Core\Database;

$strSQL="DROP TABLE IF EXISTS roles;";
$strSQL.="CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$strSQL.="DROP TABLE IF EXISTS users;";
$strSQL.="CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `role_id` int(10) unsigned NOT NULL DEFAULT 1,
  `token` varchar(45) NULL DEFAULT '',
  `expiration_token` datetime NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$strSQL.="DROP TABLE IF EXISTS emails;";
$strSQL.="CREATE TABLE `emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `subject` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `message` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `from` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `to` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `cc` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `bcc` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `reply` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `message_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `inbox` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `viewed` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `sended` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `important` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `draft` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `attachment` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `deleted` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$strSQL.="DROP TABLE IF EXISTS email_attachments;";
$strSQL.="CREATE TABLE `email_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `save_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

$strSQL.="DROP TABLE IF EXISTS notifications;";
$strSQL.="CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'received',
  `subject` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `message` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

Database::getInstance()->query($strSQL)->execute();