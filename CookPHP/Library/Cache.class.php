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

use Exception;

/**
 * 缓存类
 * @author CookPHP <admin@cookphp.org>
 */
class Cache {

    static $cache = [];

    private static function drive($drive = null) {
        $class = '\\Library\\Cache\\' . ucwords($drive ?: Config::get('cache.driver'));
        if (!isset(self::$cache[$class])) {
            if (class_exists($class)) {
                self::$cache[$class] = new $class();
            } else {
                throw new Exception('Error:Could not load cache adaptor ' . $drive . ' cache!');
            }
        }
        return self::$cache[$class];
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param string $drive 驱动
     * @return mixed
     */
    public static function get($name, $drive = null) {
        return Log::setLog('cache', 'get:' . $name, function () use ($name, $drive) {
                    return self::drive($drive)->get(self::name($name));
                });
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @param string $drive 驱动
     * @return bool
     */
    public static function set($name, $value, $expire = null, $drive = null) {
        return Log::setLog('cache', 'set:' . $name, function () use ($name, $value, $expire, $drive) {
                    return self::drive($drive)->set(self::name($name), $value, $expire);
                });
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @param string $drive 驱动
     * @return bool
     */
    public static function rm($name, $drive = null) {
        return Log::setLog('cache', 'rm:' . $name, function () use ($name, $drive) {
                    return self::drive($drive)->rm(self::name($name));
                });
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @param string $drive 驱动
     * @return bool
     */
    public static function delete($name, $drive = null) {
        return self::rm($name, $drive);
    }

    /**
     * 用户定义查询
     * @access public
     * @param string $key 缓存变量名
     * @param \closure $callable 用户定义函数
     * @param int $expire 有效时间 0为永久
     * @param string $drive 驱动
     * @return mixed
     */
    public static function remember($key, \closure $callable, $expire = null, $drive = null) {
        $data = self::get($key, $drive);
        if (!empty($data)) {
            return $data;
        } else {
            $data = $callable();
            self::set($key, $data, $expire, $drive);
            return $data;
        }
    }

    /**
     * 清除缓存
     * @access public
     * @param string $name 缓存变量名
     * @param string $drive 驱动
     * @return bool
     */
    public static function clear($drive = null) {
        return Log::setLog('cache', 'clear:', function ()use ($drive) {
                    self::drive($drive)->clear();
                });
    }

    /**
     * 返回缓存名称
     * @param string $key
     * @return string
     */
    private static function name(string $key): string {
        return md5(($_SERVER['HTTP_HOST'] ?? '') . $key);
    }

}
