<?php

/**
 * CookPHP Framework
 *
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
 * 路由操作
 * @author CookPHP <admin@cookphp.org>
 */
class Route {

    static $_controller, $_action, $_project;

    public static function init() {
        self::initApp();
    }

    private static function initApp() {
        $urls = self::explode(self::removeSuffix(PATHINFO));
        self::$_project = Config::get('route.domain') ? $this->initDomain() : ucfirst(strtolower(strip_tags(!empty(($project = array_shift($urls))) ? $project : Config::get('route.project'))));
        if (!is_dir(__CONTROLLERS__ . self::$_project)) {
            self::$_project = '';
            self::$_controller = ucfirst(strtolower(strip_tags(!empty($project) ? $project : (!empty(($controller = array_shift($urls))) ? $controller : Config::get('route.controller')))));
        } else {
            self::$_controller = ucfirst(strtolower(strip_tags(!empty(($controller = array_shift($urls))) ? $controller : Config::get('route.controller'))));
        }
        self::$_action = strtolower(strip_tags(!empty(($action = array_shift($urls))) ? $action : Config::get('route.action')));
        (!preg_match('/^[A-Za-z]+$/', self::$_controller) || !preg_match('/^[A-Za-z]+$/', self::$_action)) && Error::show404();
        defined('PROJECT') or define('PROJECT', self::getProject());
        defined('CONTROLLER') or define('CONTROLLER', self::getController());
        defined('ACTION') or define('ACTION', self::getAction());
        !empty($urls) && self::parseVar($urls);
    }

    /**
     * 返回项目名称
     * @access string
     * @return string
     */
    public static function getProject() {
        return self::$_project;
    }

    /**
     * 返回控制器名称
     * @access string
     * @return string
     */
    public static function getController() {
        return self::$_controller;
    }

    /**
     * 返回动作名称
     * @access string
     * @return string
     */
    public static function getAction() {
        return self::$_action;
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

    /**
     * 删除URL后缀
     * @access	private
     * @return	void
     */
    public static function removeSuffix($url) {
        return !empty(Config::get('route.htmlsuffix')) ? preg_replace("|" . preg_quote(Config::get('route.htmlsuffix')) . "$|", "", $url) : preg_replace('/\.' . self::ext($url) . '$/i', '', $url);
    }

    /**
     * 拆分URL
     * @access public
     * @param string $url
     * @return array
     */
    public static function explode($url) {
        $urls = [];
        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $url)) as $val) {
            $val = trim(self::filter($val));
            if ($val != '') {
                $urls[] = $val;
            }
        }
        return $urls;
    }

    /**
     * 当前URL的访问后缀
     * @access public
     * @param string $url
     * @return string
     */
    public static function ext($url = null) {
        return pathinfo($url ?: PATHINFO, PATHINFO_EXTENSION);
    }

    /**
     * 过滤段的恶意字符
     * @access public
     * @param string
     * @return string
     */
    public static function filter($str) {
        !empty($str) && !empty(Config::get('route.permittedurichars')) && !preg_match("|^[" . str_replace(['\\-', '\-'], '-', preg_quote(Config::get('route.permittedurichars'), '-')) . "]+$|i", $str) && Error::show404();
        return str_replace(['$', '(', ')', '%28', '%29'], ['&#36;', '&#40;', '&#41;', '&#40;', '&#41;'], $str);
    }

}
