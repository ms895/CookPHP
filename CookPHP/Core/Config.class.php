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
 * 配制类
 * @author CookPHP <admin@cookphp.org>
 *
 */
class Config {

    static $_config = [];

    /**
     * 返回配制
     * 优先配制 框架、公共、项目
     * @access public
     * @param string $key
     * @return mixed
     */
    public static function get($key, $default = null) {
        $file = strstr($key, '.', true) ?: $key;
        $key = trim(strrchr($key, '.'), '.');
        !isset(self::$_config[$file]) && (self::$_config[$file] = Loader::loadFile(__CONFIGS__ . $file . '.php'));
        return empty($key) ? self::$_config[$file] : (self::$_config[$file][$key] ?? $default);
    }

    /**
     * 设置配制
     * @access public
     * @param array|string $key
     * @param string    $range  作用域
     * @param mixed        $value
     */
    public static function set($key, $value = null) {
        if (is_array($key)) {
            $keys = array_change_key_case($key, CASE_LOWER);
            foreach ($keys as $key => $value) {
                $file = strstr($key, '.', true);
                $key = trim(strrchr($key, '.'), '.');
                self::$_config[$file][$key] = $value;
            }
        } else {
            if (!empty($key)) {
                $file = strstr($key, '.', true);
                $key = trim(strrchr($key, '.'), '.');
                self::$_config[$file][$key] = $value;
            }
        }
    }

}
