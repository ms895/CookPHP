<?php
define('DEBUG', true);
ini_set('display_errors', 'On');
error_reporting(E_ALL);
//ini_set('html_errors', 0);


require '../CookPHP/startup.php';

(new framework)->run();
