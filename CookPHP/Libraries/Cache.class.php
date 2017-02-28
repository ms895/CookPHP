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

namespace Libraries;

use Core\{
    Loader,
    Config,
    Log
};

/**
 * 缓存类
 * @author CookPHP <admin@cookphp.org>
 */
class Cache {

    private $_driver, $drivername;

    public static function init($driver = null) {
        static $_Cache= [];
        return $_cache[$driver] ?? ($_cache[$driver] = (new Cache($driver)));
    }

    /**
     * 补始化
     * @param string $driver 缓存驱动
     */
    public function __construct($driver = null) {
        $this->drivername = ucwords($driver === null ? Config::get('cache.driver') : $driver);
        $this->_driver = Loader::initialize(__NAMESPACE__ . '\\Cache\\' . $this->drivername);
        return $this;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return Log::setLog('Cache' . $this->drivername, 'get: ' . $name, function () use ($name) {
                    return $this->_driver->get(md5($name));
                });
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        return Log::setLog('Cache' . $this->drivername, 'set: ' . $name, function () use ($name, $value, $expire) {
                    $this->_driver->set(md5($name), $value, $expire);
                });
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return Log::setLog('Cache' . $this->drivername, 'rm: ' . $name, function () use ($name) {
                    $this->_driver->rm(md5($name));
                });
    }

    /**
     * 用户定义查询
     * @access public
     * @param string $key 缓存变量名
     * @param \closure $callable 用户定义函数
     * @param int $expire 有效时间 0为永久
     * @return mixed
     */
    public function remember($key, \closure $callable, $expire = null) {
        $data = $this->get($key);
        if ($data) {
            return $data;
        } else {
            $data = $callable();
            $this->set($key, $data, $expire);
            return $data;
        }
    }

    /**
     * 清除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function clear() {
        return Log::setLog('Cache' . $this->drivername, 'clear: ', function () {
                    $this->_driver->clear();
                });
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    public function __unset($name) {
        $this->rm($name);
    }

}
