<?php

use Engine\Route;

define('DEBUG', true);

//入口目录
define('__FILENAME__', __DIR__ . DIRECTORY_SEPARATOR);


require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CookPHP' . DIRECTORY_SEPARATOR . 'Startup.php';

Route::start();
