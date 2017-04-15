<?php

/**
 * CookPHP Framework
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link <a href="http://www.cookphp.org">CookPHP</a>
 * @copyright cookphp.org
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Engine;

use Library\{
    Config,
    Input,
    Url,
    Exception,
    Cookie
};

/**
 * 执行项目
 */
class Action {

    private static $route;
    private static $controller;
    private static $method;

    private static function init() {
//        if (PATHINFO === 'favicon.ico') {
//            echo '';
//        }
        $parts = Url::explode(Url::removeSuffix(PATHINFO));
        self::initLanguage($parts);
        self::$route = ucfirst(strtolower(strip_tags(!empty(($project = array_shift($parts))) ? $project : Config::get('route.project'))));
        if (!is_dir(__CONTROLLERS__ . self::$route)) {
            self::$route = '';
            self::$controller = ucfirst(strtolower(strip_tags(!empty($project) ? $project : (!empty(($controller = array_shift($parts))) ? $controller : Config::get('route.controller')))));
        } else {
            self::$controller = ucfirst(strtolower(strip_tags(!empty(($controller = array_shift($parts))) ? $controller : Config::get('route.controller'))));
        }
        self::$method = strtolower(strip_tags(!empty(($action = array_shift($parts))) ? $action : Config::get('route.action')));
        !empty($parts) && self::parseVar($parts);
        (!preg_match('/^[A-Za-z]+$/', self::$controller) || !preg_match('/^[A-Za-z]+$/', self::$method)) && self::show404();
        defined('APP_ROUTE') or define('APP_ROUTE', self::getRoute());
        defined('APP_CONTROLLER') or define('APP_CONTROLLER', self::getController());
        defined('APP_METHOD') or define('APP_METHOD', self::getMethod());
    }

    /**
     * 初始语言包
     */
    private static function initLanguage(&$urls) {
        $key = Config::get('default.langvar');
        $lang = !empty($urls[0]) && Config::get('default.langarray') && key_exists($urls[0], Config::get('default.langarray')) ? array_shift($urls) : ($_GET[$key] ?? (Cookie::get($key) ?: (preg_match('/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_OFFSET_CAPTURE) && isset($matches[1][0]) ? strtolower($matches[1][0] . (isset($matches[3][0]) ? '-' . $matches[3][0] : '')) : Config::get('default.language'))));
        defined('LANGUAGE') or define('LANGUAGE', strtolower(Config::get('default.langarray') && key_exists($lang, Config::get('default.langarray')) ? $lang : Config::get('default.language')));
        Cookie::set($key, LANGUAGE);
    }

    /**
     * 返回项目名称
     * @access string
     * @return string
     */
    public static function getRoute() {
        return self::$route;
    }

    /**
     * 返回控制器名称
     * @access string
     * @return string
     */
    public static function getController() {
        return self::$controller;
    }

    /**
     * 返回动作名称
     * @access string
     * @return string
     */
    public static function getMethod() {
        return self::$method;
    }

    public static function execute() {
        self::init();
        if (substr(self::getMethod(), 0, 2) == '__') {
            self::show404();
        }
        try {
            $appClass = '\\Controller\\' . (APP_ROUTE ? APP_ROUTE . '\\' : '') . APP_CONTROLLER;
            $class = new \ReflectionClass($appClass);
            $method = $class->getmethod(APP_METHOD);
            if ($method->isPublic() && !$method->isStatic()) {
                if (IS_POST && $class->hasMethod('post_' . APP_METHOD)) {
                    $post = $class->getMethod('post_' . APP_METHOD);
                    $post->isPublic() && !$post->isStatic() && self::invoke($class, $post);
                } elseif (IS_PUT && $class->hasMethod('put_' . APP_METHOD)) {
                    $put = $class->getMethod('put_' . APP_METHOD);
                    $put->isPublic() && !$put->isStatic() && self::invoke($class, $put);
                } elseif (IS_DELETE && $class->hasMethod('delete_' . APP_METHOD)) {
                    $delete = $class->getMethod('delete_' . APP_METHOD);
                    $delete->isPublic() && !$delete->isStatic() && self::invoke($class, $delete);
                } else {
                    if ($class->hasMethod('before_' . APP_METHOD)) {
                        $before = $class->getMethod('before_' . APP_METHOD);
                        $before->isPublic() && !$before->isStatic() && self::invoke($class, $before);
                    }
                    self::invoke($class, $method);
                    if ($class->hasMethod('after_' . APP_METHOD)) {
                        $after = $class->getMethod('after_' . APP_METHOD);
                        $after->isPublic() && self::invoke($class, $after);
                    }
                }
            }
        } catch (Exception $e) {
            exit($e->getMessage());
            self::show404();
        }
    }

    private static function invoke($class, $method) {
        if ($method->getNumberOfParameters() > 0) {
            $args = [];
            $vars = Input::param();
            foreach ($method->getParameters() as $key => $param) {
                $args[] = $vars[$param->getName()] ?? ($vars[$key] ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null));
            }
            $method->invokeArgs($class->newInstanceArgs(), $args);
        } else {
            $method->invoke($class->newInstanceArgs());
        }
    }

    /**
     * 解释 Var
     * @access public
     */
    public static function parseVar($url) {
        if (!empty($url)) {
            preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use(&$var) {
                $var[strtolower($match[1])] = strip_tags($match[2]);
            }, implode('/', $url));
            if (!empty($var)) {
                $_GET = array_merge($var, $_GET);
            }
        }
    }

    public static function show404() {
        echo time();
        //Exception::show404();
    }

}
