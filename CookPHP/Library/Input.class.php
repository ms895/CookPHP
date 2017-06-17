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

namespace Library;

/**
 * 输入操作类
 * @author CookPHP <admin@cookphp.org>
 */
class Input {

    /**
     * 获取GET
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function get(string $key = null, string $filter = null) {
        return is_null($key) ? self::varFilter($_GET, $filter) : self::varFilter($_GET[$key] ?? null, $filter);
    }

    /**
     * 获取POST
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function post(string $key = null, string $filter = null) {
        return is_null($key) ? self::varFilter($_POST, $filter) : self::varFilter($_POST[$key] ?? null, $filter);
    }

    /**
     * 获取PUT
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function put(string $key = null, string $filter = null) {
        static $_put = null;
        if ($_put === null) {
            parse_str(file_get_contents('php://input'), $_put);
        }
        return is_null($key) ? self::varFilter($_put, $filter) : self::varFilter($_put[$key] ?? null, $filter);
    }

    /**
     * 初始HTTP_RAW_POST_DATA
     * 考虑到PHP7默认禁止 HTTP_RAW_POST_DATA 如微信支付时
     * @access public
     */
    public static function httpRawPostData() {
        if (IS_POST && empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents("php://input");
        }
    }

    /**
     * 获取Ddlete
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function delete(string $key = null, string $filter = null) {
        static $_delete = null;
        if (is_null($_delete)) {
            parse_str(file_get_contents('php://input'), $_delete);
        }
        return is_null($key) ? self::varFilter($_delete, $filter) : self::varFilter($_delete[$key] ?? null, $filter);
    }

    /**
     * 获取COOKIE
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function cookie(string $key = null, string $filter = null) {
        return is_null($key) ? self::varFilter($_COOKIE, $filter) : self::varFilter($_COOKIE[$key] ?? null, $filter);
    }

    /**
     * 获取REQUEST
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function request(string $key = null, string $filter = null) {
        return is_null($key) ? self::varFilter($_REQUEST, $filter) : self::varFilter($_REQUEST[$key] ?? null, $filter);
    }

    /**
     * 获取SERVER
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function server(string $key = null, string $filter = null) {
        return is_null($key) ? self::varFilter($_SERVER, $filter) : self::varFilter($_SERVER[$key] ?? null, $filter);
    }

    /**
     * 获取ENV
     * @access public
     * @param string|null $key 名称 为空时返回所有
     * @param string $filter 安全过滤方法
     * @return mixed
     */
    public static function env(string $key = null, string $filter = null) {
        return is_null($key) ? self::varFilter($_ENV, $filter) : self::varFilter($_ENV[$key] ?? null, $filter);
    }

    /**
     * 参数过滤方法
     * @access public
     * @param string|array $content 过滤内容
     * @param string $filter 过滤方法
     * @return mixed
     */
    public static function varFilter($content, string $filter = null) {
        if (empty($content)) {
            return $content;
        }
        return is_array($content) ? array_map(function ($a) use (&$filter ) {
                    return self::varFilter($a, $filter);
                }, $content) : trim($filter ? $filter($content) : $content);
    }

    /**
     * 当前请求的参数
     * @access public
     * @param string $name 变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function param($name = '', $default = null) {
        $vars = IS_POST ? self::post() : (IS_PUT ? self::put() : (IS_DELETE ? self::delete() : []));
        $param = self::get() ? array_merge(self::get(), $vars) : $vars;
        return $name ? ($param[$name] ?? $default) : ($param ?: $default);
    }

    /**
     * 返回客户端IP
     * @access public
     * @return string
     */
    public static function ip(): string {
        return get_ip();
    }

}
