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

namespace Library\Cache;

use Library\Config;
use Exception;

/**
 * Apc缓存驱动
 */
class Apc {

    private $config = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        if (!function_exists('apc_cache_info')) {
            throw new Exception('Error Cache Handler:Apc');
        }
        $this->config['prefix'] = Config::get('cache.prefix');
        $this->config['expire'] = Config::get('cache.expire');
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return apc_fetch($this->config['prefix'] . $name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->config['expire'];
        }
        $name = $this->config['prefix'] . $name;
        return apc_store($name, $value, $expire);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return apc_delete($this->config['prefix'] . $name);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return apc_clear_cache('user');
    }

}
