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
 * 缓存类
 * @author CookPHP <admin@cookphp.org>
 */
class Cache {

    private $adaptor, $drivername;
    static $cache = [];

    public function __construct($adaptor = null) {
        $class = '\\Library\\Cache\\' . ucwords($adaptor ?: Config::get('cache.driver'));
        if (!isset(self::$cache[$class])) {
            if (class_exists($class)) {
                self::$cache[$class] = new $class();
            } else {
                throw new \Exception('Error: Could not load cache adaptor ' . $adaptor . ' cache!');
            }
            $this->drivername = $adaptor;
        }
        $this->adaptor = self::$cache[$class];
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return Log::setLog('cache' . $this->drivername, 'get: ' . $name, function () use ($name) {
                    return $this->adaptor->get($this->name($name));
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
        return Log::setLog('cache' . $this->drivername, 'set: ' . $name, function () use ($name, $value, $expire) {
                    $this->adaptor->set($this->name($name), $value, $expire);
                });
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return Log::setLog('cache' . $this->drivername, 'rm: ' . $name, function () use ($name) {
                    $this->adaptor->rm($this->name($name));
                });
    }

    public function delete($key) {
        return $this->rm($key);
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
        return Log::setLog('cache' . $this->drivername, 'clear: ', function () {
                    $this->adaptor->clear();
                });
    }

    /**
     * 返回缓存名称
     * @param string $key
     * @return string
     */
    private function name(string $key): string {
        return md5(($_SERVER['HTTP_HOST'] ?? '') . $key);
    }

}
