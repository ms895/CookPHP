<?php

//检测是PHP版本
version_compare(PHP_VERSION, '7.0.0', 'ge') or die('require PHP >= 7.0.0 !');
/**
 * 加载Constants
 */
require 'Constants.php';
/**
 * 开户报错
 */
error_reporting(E_ALL);
/**
 * 加载Helper
 */
require 'Helper' . DS . 'Common.php';
/**
 * 注册AUTOLOAD
 */
spl_autoload_register(function ($class) {
    $init = explode('\\', $class, 2);
    in_array($init[0], ['Engine', 'Library']) ? (file_exists($file = __COOK__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') ? require $file : false) : (file_exists($file = __APP__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') ? require $file : false);
});
register_shutdown_function(function () {
    if (($e = error_get_last())) {
        Library\Error::show($e);
    }
});
/**
 * 注册错误
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Library\Error::show("[{$errno}] {$errstr} {$errfile} On line {$errline}.");
});
/**
 * 注册异常处理
 */
set_exception_handler(function($e) {
    $error = [];
    $error['message'] = $e->getMessage();
    $trace = $e->getTrace();
    if ('E' == $trace[0]['function']) {
        $error['file'] = $trace[0]['file'];
        $error['line'] = $trace[0]['line'];
    } else {
        $error['file'] = $e->getFile();
        $error['line'] = $e->getLine();
    }
    $error['trace'] = $e->getTraceAsString();
    Library\Error::show($error);
});
