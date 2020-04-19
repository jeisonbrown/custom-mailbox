<?php 


use \Core\Database;

$password = sha1("1234");

$strSQL="INSERT INTO roles (id, name) VALUES (1, 'Super Admin');";
$strSQL.="INSERT INTO roles (id, name) VALUES (2, 'Admin');";
$strSQL.="INSERT INTO roles (id, name) VALUES (3, 'User');";

$strSQL.="INSERT INTO users (id, name, email, password, role_id) values (1, 'Santiago Ruiz', 'sanruiz1003@gmail.com', '{$password}', 1);";
$strSQL.="INSERT INTO users (id, name, email, password, role_id) values (2, 'Jeison Jose Brown Mille', 'jeisonbrownmiller@gmail.com', '{$password}', 1);";

$strSQL.="INSERT INTO emails (id, user_id, subject, message, name, `from`, `to`, inbox, important, attachment) values (1, 1, 'Bienvenido!', 'Bienvenido al sistema de Mailbox', 'Mailbox', 'inbox@mailbox.com', 'sanruiz1003@gmail.com', 1, 1, 1);";
$strSQL.="INSERT INTO emails (id, user_id, subject, message, name, `from`, `to`, inbox, important, attachment) values (2, 2, 'Bienvenida', 'Bienvenido al sistema de Mailbox', 'Mailbox', 'inbox@mailbox.com', 'jeisonbrownmiller@gmail.com', 1, 1, 1);";

$strSQL.="INSERT INTO email_attachments (id, email_id, name, save_name) values (1, 1, 'mailbox.txt', 'mailbox.txt');";
$strSQL.="INSERT INTO email_attachments (id, email_id, name, save_name) values (2, 2, 'mailbox.txt', 'mailbox.txt');";

$strSQL.="INSERT INTO notifications (id, user_id, subject, message, url) values (1, 1, 'Nuevo mensaje', 'Has recibido un nuevo mensaje!', '/1');";
$strSQL.="INSERT INTO notifications (id, user_id, subject, message, url) values (2, 2, 'Nuevo mensaje', 'Has recibido un nuevo mensaje!', '/2');";

Database::getInstance()->query($strSQL)->execute();