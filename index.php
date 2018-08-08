<?php
require_once('database/MySQL.php');
require_once('counter/Counter.php');
MySQL::connect();
MySQL::SQL("INSERT INTO `test`(`country`, `event`) VALUES ('John', 'Doe')");

?>
