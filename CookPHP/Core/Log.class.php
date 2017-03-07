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
 * 日志类
 * @author CookPHP <admin@cookphp.org>
 */
class Log {

    static private $_log = [];

    /**
     * 返回使用时间
     * @access public
     * @param int $start
     * @param int $end
     * @param int $dec
     */
    public static function getUsageTime($start = null, $end = null, $dec = 4) {
        return number_format((($end ?: microtime(true)) - ($start ?: START_TIME)), $dec);
    }

    /**
     * 返回用户使用内存
     * @access public
     * @param int $start
     * @param int $end
     */
    public static function getUsageMemory($start = null, $end = null) {
        return function_exists('memory_get_usage') ? \Libraries\Format::size(($end ?: memory_get_usage()) - ($start ?: START_MEMORY)) : null;
    }

    /**
     * 创建执行日志
     * @param string $type
     * @param string $message
     * @param \closure $callable
     * @return $callable
     */
    public static function setLog($type, $message, \closure $callable) {
        $time = microtime(true);
        $function = $callable();
        self::$_log[$type][] = [self::getUsageTime($time), $message];
        return $function;
    }

    /**
     * 返回日志
     * @param string $type
     * @return array
     */
    public static function getLog($type = null): array {
        return $type === null ? self::$_log : ( self::$_log[$type] ?? []);
    }

    /**
     * 返回最后一个日志
     * @access public
     * @param string $type
     * @return array
     */
    public static function lastLog($type): array {
        return isset(self::$_log[$type]) ? end(self::$_log[$type]) : [];
    }

    public static function display() {
        $info = '';
        foreach (self::$_log as $key => $value) {
            foreach ($value as $vo) {
                $info .= $key . ' : ' . $vo[0] . ' ' . $vo[1] . '<br/>';
            }
        }
        echo <<<EOD
<div id="cook_page_trace" style="background:white;margin:6px;font-size:14px;border:1px dashed silver;padding:8px"><fieldset id="querybox" style="margin:5px;"><legend style="color:gray;font-weight:bold">日志</legend><div style="overflow:auto;text-align:left;">{$info}</div></fieldset></div>
EOD;
    }

}
