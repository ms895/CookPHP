<?php

/**
 * CookPHP Framework
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href="http://www.cookphp.org">CookPHP</a>
 */

namespace Core;

/**
 * 项目入口
 * @author CookPHP <admin@cookphp.org>
 */
class Application {

    /**
     * 运行项目
     * @access private
     */
    public static function run() {
        self::init();
        $appClass = '\\Controller\\' . (PROJECT ? PROJECT . '\\' : '') . CONTROLLER;
        (!class_exists($appClass) || !in_array(ACTION, get_class_methods($appClass))) && Error::show404();
        self::invokeMethod([$appClass, ACTION]);
    }

    /**
     * 初始化项目
     * @access private
     */
    public static function init() {
        if (DEBUG) {
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);
            ini_set('html_errors', 0);
        } else {
            error_reporting(null);
            ini_set('display_errors', 'Off');
        }
        date_default_timezone_set(Config::get('default.timezone'));
        IS_CLI && self::parseArgv();
        Route::init();
        self::initSession();
    }

    /**
     * 初始Session
     * @access private
     */
    private static function initSession() {
        Config::get('session.start') && \Libraries\Session::init();
    }

    /**
     * 解释 argv
     * @access private
     */
    private static function parseArgv() {
        if (isset($_SERVER['argv'][2])) {
            parse_str($_SERVER['argv'][2], $_GET);
            parse_str($_SERVER['argv'][2], $_REQUEST);
        }
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $methods 方法
     * @param array        $vars   变量
     * @return mixed
     */
    public static function invokeMethod($methods, $vars = []) {
        try {
            $class = new \ReflectionClass($methods[0]);
            if (!empty($methods[1])) {
                $method = $class->getmethod($methods[1]);
                if ($method->getNumberOfParameters() > 0) {
                    empty($vars) && ($vars = Input::param());
                    $args = [];
                    foreach ($method->getParameters() as $key => $param) {
                        $name = $param->getName();
                        $args[] = $vars[$name] ?? ($vars[$key] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null));
                    }
                    return $method->invokeArgs($class->newInstanceArgs(), $args);
                } else {
                    return $method->invoke($class->newInstanceArgs());
                }
            } else {
                return $class->newInstanceArgs($vars);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
            Error::showError($e->getMessage());
        }
    }

    /**
     * 字符串命名风格转换
     * type false 将Java风格转换为C的风格 true 将C风格转换为Java的风格
     * @param string  $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    public static function parseName($name, $type = false) {
        return $type ? ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                            return strtoupper($match[1]);
                        }, $name)) : strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

}
