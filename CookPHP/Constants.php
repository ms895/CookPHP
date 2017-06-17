<?php

/**
 * 常量
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link <a href="http://www.cookphp.org">CookPHP</a>
 * @copyright cookphp.org
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */
//框架开始时间
define('START_TIME', microtime(true));
//框架开始内存
define('START_MEMORY', memory_get_usage());
//框架版本
define('VERSION', '0.0.1');
//框架调试
defined('DEBUG') or define('DEBUG', false);
//REWRITE模式
defined('REWRITE') or define('REWRITE', true);
//框架编码
defined('CHARSET') or define('CHARSET', 'utf-8');
//返回访问页面使用的请求方法
defined('REQUEST_METHOD') or define('REQUEST_METHOD', strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'));
//返回浏览器相关参数
defined('USER_AGENT') or define('USER_AGENT', $_SERVER['HTTP_USER_AGENT'] ?? '');
//返回当前PHP是否是64位
defined('IS_64BIT') or define('IS_64BIT', PHP_INT_SIZE === 8);
//检测是否为CGI PHP
defined('IS_CGI') or define('IS_CGI', substr(PHP_SAPI, 0, 3) === 'cgi');
//检测是否Win
defined('IS_WIN') or define('IS_WIN', strstr(PHP_OS, 'WIN'));
//检测是否为CLI PHP
defined('IS_CLI') or define('IS_CLI', substr(PHP_SAPI, 0, 3) === 'cli');
//返回是否GET请求
defined('IS_GET') or define('IS_GET', REQUEST_METHOD === 'GET' ? true : false);
//返回是否POST请求
defined('IS_POST') or define('IS_POST', REQUEST_METHOD === 'POST' ? true : false);
//返回是否PUT请求
defined('IS_PUT') or define('IS_PUT', REQUEST_METHOD === 'PUT' ? true : false);
//返回是否DELETE请求
defined('IS_DELETE') or define('IS_DELETE', REQUEST_METHOD === 'DELETE' ? true : false);
//返回是否AJAX请求
defined('IS_AJAX') or define('IS_AJAX', strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest');
//简化 DS
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
//框架路径
defined('__COOK__') or define('__COOK__', __DIR__ . DS);
//APP目录
defined('__APP__') or define('__APP__', dirname(__COOK__) . DS . 'Application' . DS);
//配制目录
defined('__CONFIGS__') or define('__CONFIGS__', __APP__ . 'Config' . DS);
//语言目录
defined('__LANGUAGE__') or define('__LANGUAGE__', __APP__ . 'Language' . DS);
//控制器目录
defined('__CONTROLLERS__') or define('__CONTROLLERS__', __APP__ . 'Controller' . DS);
//模型目录
defined('__MODELS__') or define('__MODELS__', __APP__ . 'Model' . DS);
//视图目录
defined('__VIEWS__') or define('__VIEWS__', __APP__ . 'View' . DS);
//错误目录
defined('__ERROR__') or define('__ERROR__', __APP__ . 'Error' . DS);
//库目录
defined('__LIBRARIES__') or define('__LIBRARIES__', __APP__ . 'Librarie' . DS);
//运行时读写目录
defined('__STORAGE__') or define('__STORAGE__', __APP__ . 'Runtime' . DS . 'Storage' . DS);
//临时目录
defined('__TMP__') or define('__TMP__', __STORAGE__ . 'Tmp' . DS);
//缓存目录
defined('__CACHE__') or define('__CACHE__', __STORAGE__ . 'Cache' . DS);
//日志目录
defined('__LOGS__') or define('__LOGS__', __STORAGE__ . 'Logs' . DS);
//数据目录
defined('__DATA__') or define('__DATA__', __COOK__ . 'Data' . DS);
//获取当前请求PATH_INFO
defined('PATHINFO') or define('PATHINFO', IS_CLI ? ($_SERVER['argv'][1] ?? '') : ($_SERVER['PATH_INFO'] ?? (!empty($_SERVER['ORIG_PATH_INFO']) ? ((0 === strpos($_SERVER['ORIG_PATH_INFO'], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER['ORIG_PATH_INFO'], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER['ORIG_PATH_INFO']) : (!empty($_SERVER['REDIRECT_PATH_INFO']) ? ((0 === strpos($_SERVER['REDIRECT_PATH_INFO'], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER['REDIRECT_PATH_INFO'], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER['REDIRECT_PATH_INFO']) : (!empty($_SERVER['REDIRECT_URL']) ? ((0 === strpos($_SERVER['REDIRECT_URL'], $_SERVER['SCRIPT_NAME'])) ? substr($_SERVER['REDIRECT_URL'], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER['REDIRECT_URL']) : '')))));
//入口目录
defined('__FILENAME__') or define('__FILENAME__', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
