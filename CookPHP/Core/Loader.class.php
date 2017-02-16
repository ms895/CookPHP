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
 * 加载类
 * @author CookPHP <admin@cookphp.org>
 *
 */
class Loader {

    /**
     * 加载类
     * @access public
     * @param string $class
     * @return bool
     */
    public static function loadClass($class) {
        $init = explode('\\', $class, 2);
        if (in_array($init[0], ['Core', 'Libraries'])) {
            if (self::requireOnce(__COOK__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php')) {
                return true;
            }
        }
        if (in_array($init[0], [basename(__CONTROLLERS__), basename(__MODELS__), basename(__LIBRARIES__)])) {
            if (self::requireOnce(__APP__ . PROJECT . DS . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php')) {
                return true;
            }
        }
        return self::requireOnce(__APP__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') || self::requireOnce(__COOK__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php');
    }

    /**
     * 获取返回文件
     * @access public
     * @param string $file
     * @return mixed
     */
    public static function loadFile($file) {
        return file_exists($file) ? require $file : null;
    }

    /**
     * 唯一包含并运行指定文件
     * @access public
     * @param string $file
     * @return bool
     */
    public static function requireOnce($file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

    /**
     * 加载文件
     * @param string $file
     * @return bool 
     */
    public static function requireFile($file) {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param string|array $method 方法
     * @param array        $vars   变量
     * @return mixed
     */
    public static function initialize($method, $vars = []) {
        return Application::invokeMethod((array) $method, $vars);
    }

    /**
     * 实例model
     * @access public
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    public static function model($table = null, $config = []) {
        static $_model = [];
        return $_model[$table] ?? ($_model[$table] = (!empty($table) && class_exists(($newtable = '\\Model\\' . Application::parseName($table, true))) ? self::initialize($newtable, [null, $config]) : self::initialize('\\Core\\Model', [$table ?: null, $config])));
    }

    /**
     * 解析URL
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    public static function url(string $url, $params = [], $domain = false) {
        return Url::parse($url, $params, $domain);
    }

    /**
     * URl跳转
     * @param string $url
     */
    public static function header($url) {
        exit(header('Location:' . $url));
    }

    /**
     * URl跳转 解析地址
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    public static function headerUrl(string $url, $params = [], $domain = false) {
        self::header(self::url($url, $params, $domain));
    }

    /**
     * 返回缓存
     * @access public
     * @param string $driver 驱动
     * @return \Core\Cache
     */
    public static function cache($driver = null) {
        return \Libraries\Cache::init($driver);
    }

}
