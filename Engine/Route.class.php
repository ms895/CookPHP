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
    Cookie,
    Session,
    Input,
    Error
};
use Exception;

/**
 * 路由解析类
 * @author CookPHP <admin@cookphp.org>
 */
class Route {

    private static $route, $controller, $method;

    /**
     * 初始项目
     * @access public
     */
    public static function init() {
        self::initTimezone();
        self::initSession();
        self::initLanguage($parts);
        self::parseParts($parts);
        self::parseVar($parts);
    }

    /**
     * 开始项目
     * @access public
     */
    public static function start() {
        self::init();
        if (substr(APP_METHOD, 0, 2) == '__') {
            Error::notFound();
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
            Error::notFound();
        }
    }

    /**
     * 初始时区
     * @access public
     */
    private static function initTimezone() {
        date_default_timezone_set(Config::get('default.timezone'));
    }

    /**
     * 初始session
     * @access public
     */
    private static function initSession() {
        Config::get('session.start') && Session::init();
    }

    /**
     * 返回项目名称
     * @access public
     * @return string
     */
    public static function getRoute() {
        return self::$route;
    }

    /**
     * 返回控制器名称
     * @access public
     * @return string
     */
    public static function getController() {
        return self::$controller;
    }

    /**
     * 返回动作名称
     * @access public
     * @return string
     */
    public static function getMethod() {
        return self::$method;
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

    private static function parseParts(&$parts) {
        self::$route = ucfirst(strtolower(strip_tags(!empty(($project = array_shift($parts))) ? $project : Config::get('route.project'))));
        if (!is_dir(__CONTROLLERS__ . self::$route)) {
            self::$route = '';
            self::$controller = ucfirst(strtolower(strip_tags(!empty($project) ? $project : (!empty(($controller = array_shift($parts))) ? $controller : Config::get('route.controller')))));
        } else {
            self::$controller = ucfirst(strtolower(strip_tags(!empty(($controller = array_shift($parts))) ? $controller : Config::get('route.controller'))));
        }
        self::$method = strtolower(strip_tags(!empty(($action = array_shift($parts))) ? $action : Config::get('route.action')));
        (!preg_match('/^[A-Za-z]+$/', self::$controller) || !preg_match('/^[A-Za-z]+$/', self::$method)) && Error::notFound();
        defined('APP_ROUTE') or define('APP_ROUTE', self::$route);
        defined('APP_CONTROLLER') or define('APP_CONTROLLER', self::$controller);
        defined('APP_METHOD') or define('APP_METHOD', self::$method);
    }

    /**
     * 初始语言包
     * @access private
     */
    private static function initLanguage(&$urls) {
        $urls = self::explode(self::removeSuffix(PATHINFO));
        $key = Config::get('default.langvar');
        $lang = !empty($urls[0]) && Config::get('default.langarray') && key_exists($urls[0], Config::get('default.langarray')) ? array_shift($urls) : ($_GET[$key] ?? (Cookie::get($key) ?: (preg_match('/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_OFFSET_CAPTURE) && isset($matches[1][0]) ? strtolower($matches[1][0] . (isset($matches[3][0]) ? '-' . $matches[3][0] : '')) : Config::get('default.language'))));
        defined('LANGUAGE') or define('LANGUAGE', strtolower(Config::get('default.langarray') && key_exists($lang, Config::get('default.langarray')) ? $lang : Config::get('default.language')));
        Cookie::set($key, LANGUAGE);
        unset($lang);
    }

    /**
     * 解释 Var
     * @access private
     */
    private static function parseVar(&$url) {
        if (!empty($url)) {
            preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use(&$var) {
                $var[strtolower($match[1])] = strip_tags($match[2]);
            }, implode('/', $url));
            if (!empty($var)) {
                $_GET = array_merge($var, $_GET);
            }
        }
        $url = null;
    }

    /**
     * 过滤段的恶意字符
     * @access private
     * @param string
     * @return string
     */
    private static function filter($str) {
        !empty($str) && !empty(Config::get('route.permittedurichars')) && !preg_match("|^[" . str_replace(['\\-', '\-'], '-', preg_quote(Config::get('route.permittedurichars'), '-')) . "]+$|i", $str) && Error::notFound();
        return str_replace(['$', '(', ')', '%28', '%29'], ['&#36;', '&#40;', '&#41;', '&#40;', '&#41;'], $str);
    }

    /**
     * 删除URL后缀
     * @access	private
     * @return	void
     */
    private static function removeSuffix($url) {
        return !empty(Config::get('route.htmlsuffix')) ? preg_replace("|" . preg_quote(Config::get('route.htmlsuffix')) . "$|", "", $url) : preg_replace('/\.' . self::ext($url) . '$/i', '', $url);
    }

    /**
     * 拆分URL
     * @access private
     * @param string $url
     * @param string $dept
     * @return array
     */
    private static function explode($url, $dept = null) {
        $urls = [];
        foreach (explode($dept ?: ( Config::get('route.pathinfodepr') ?: '/'), preg_replace("|/*(.+?)/*$|", "\\1", $url)) as $val) {
            $val = trim(self::filter($val));
            if ($val != '') {
                $urls[] = $val;
            }
        }
        return $urls;
    }

    /**
     * 当前URL的访问后缀
     * @access private
     * @param string $url
     * @return string
     */
    private static function ext($url = null) {
        return pathinfo($url ?: PATHINFO, PATHINFO_EXTENSION);
    }

}
