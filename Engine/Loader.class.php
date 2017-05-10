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
    Language,
    Config,
    Url
};
use Exception;

/**
 * 装载机
 */
abstract class Loader {

    /**
     * 实例model
     * @access protected
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    protected function model(string $table = null, $config = []) {
        static $_model = [];
        if (!isset($_model[$table])) {
            if (!empty($table) && class_exists(($newtable = '\\Model\\' . parse_name(preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $table), true)))) {
                $_model[$table] = new $newtable(null, $config);
            } else {
                $_model[$table] = new Model($table ?: null, $config);
            }
        }

        return $_model[$table];
    }

    /**
     * 初始视图
     * @return View
     */
    protected function view() {
        static $view = null;
        return $view ?: $view = new View();
    }

    /**
     * 初始Library
     * @access protected
     * @param string $route
     * @return library
     * @throws \Exception
     */
    protected function library(string $route) {
        static $library = [];
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $route);
        if (!isset($library[$route])) {
            try {
                $class = '\\Library\\' . $route;
                $library[$route] = new $class;
            } catch (Exception $e) {
                throw new Exception('Error: Could not load library ' . $route . '!');
            }
        }
        return $library[$route];
    }

    /**
     * 加载帮助函数
     * @access protected
     * @param string $route
     * @throws \Exception
     */
    protected function helper(string $route) {
        if (file_exists($file = __COOK__ . 'Helper' . DS . preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $route) . '.php')) {
            include_once($file);
        } else {
            throw new Exception('Error: Could not load helper ' . $route . '!');
        }
    }

    /**
     * 重定向
     * @access protected
     * @param string $url
     * @param bool $parse
     * @param int $status
     */
    protected function redirect(string $url, bool $parse = true, int $status = 302) {
        exit(header('Location: ' . ($parse ? $this->url($url) : str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url)), true, $status));
    }

    /**
     * 解析URL
     * @access protected
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    protected function url(string $url, $params = [], bool $domain = false) {
        return Url::parse($url, $params, $domain);
    }

    /**
     * 返回语言
     * @access protected
     * @param string $key
     * @return mixed
     */
    protected function language(string $key, $default = null) {
        return Language::get($key, $default);
    }

    /**
     * 返回配制
     * @access protected
     * @param string $key
     * @return mixed
     */
    protected function config(string $key, $default = null) {
        return Config::get($key, $default);
    }

}
