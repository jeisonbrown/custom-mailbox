<?php 


use \Core\Database;

$password = sha1("1234");

$strSQL="INSERT INTO users (name, email, password) values ('Santiago Ruiz', 'sanruiz1003@gmail.com', '{$password}');";
$strSQL.="INSERT INTO users (name, email, password) values ('Jeison Jose Brown Mille', 'jeisonbrownmiller@gmail.com', '{$password}');";

Database::getInstance()->query($strSQL)->execute();