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

/**
 * 装载机
 */
abstract class Loader {

    static $registry, $library;

    /**
     * 实例model
     * @access protected
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    protected function model(string $table = null, $config = []) {
        return model($table, $config);
    }

    /**
     * 渲染模板
     * @access protected
     */
    protected function view(string $adaptor = null) {
        return view($adaptor);
    }

    /**
     * 初始Library
     * @access protected
     * @param string $route
     * @return library
     * @throws \Exception
     */
    protected function library(string $route) {
        return library($route);
    }

    /**
     * 初始缓存
     * @param string $adaptor
     * @param int $expire
     * @return Cache
     */
    protected function cache(string $adaptor = null, int $expire = 3600) {
        return cache($adaptor, $expire);
    }

    /**
     * 加载帮助函数
     * @param string $route
     * @throws \Exception
     */
    protected function helper(string $route) {
        return helper($route);
    }

    /**
     * 返回配制
     * @access public
     * @param string $key
     * @return mixed
     */
    protected function config($key, $default = null) {
        return config($key, $default);
    }

    /**
     * 返回语言
     * @access public
     * @param string $key
     * @return mixed
     */
    protected function language($key, $default = null) {
        return language($key, $default);
    }

    /**
     * 解析URL
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    protected function url(string $url, $params = [], bool $domain = false) {
        return url($url, $params, $domain);
    }

}
