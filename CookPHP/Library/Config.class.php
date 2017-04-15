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

namespace Library;

/**
 * 配制
 */
class Config {

    private static $data = [];

    /**
     * 返回配制
     * @access public
     * @param string $key
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::formatName($key, $file, $name);
        return empty($name) ? self::$data[$file] : (self::$data[$file][$name] ?? $default);
    }

    /**
     * 设置配制
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::formatName($key, $file, $name);
        self::$data[$file][$name] = $value;
    }

    /**
     * 返回配制是否存在
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        self::formatName($key, $file, $name);
        return isset(self::$data[$file][$name]);
    }

    /**
     * 加载配制
     * @param string $filename
     * @return mixed
     */
    public static function load($filename) {
        if (file_exists($file = __CONFIGS__ . $filename . '.php')) {
            self::$data[$filename] = isset(self::$data[$filename]) ? array_merge(self::$data[$filename], require($file)) : require($file);
            return true;
        } else {
            exit(trigger_error('Error: Could not load config ' . $filename . '!'));
        }
    }

    /**
     * 格式化名称
     * @param string $key
     * @param string $file
     * @param string $name
     */
    public static function formatName($key, &$file, &$name) {
        $file = strstr($key, '.', true) ?: $key;
        $name = trim(strrchr($key, '.'), '.');
        !isset(self::$data[$file]) && self::load($file);
    }

}
