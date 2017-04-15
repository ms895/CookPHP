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
 * 代理
 */
class Proxy {

    public function __get($key) {
        return $this->{$key};
    }

    public function __set($key, $value) {
        $this->{$key} = $value;
    }

    public function __call($key, $args) {
        $arg_data = [];

        $args = func_get_args();

        foreach ($args as $arg) {
            if ($arg instanceof Ref) {
                $arg_data[] = & $arg->getRef();
            } else {
                $arg_data[] = & $arg;
            }
        }

        if (isset($this->{$key})) {
            return call_user_func_array($this->{$key}, $arg_data);
        } else {
            $trace = debug_backtrace();

            exit('<b>Notice</b>:  Undefined property: Proxy::' . $key . ' in <b>' . $trace[1]['file'] . '</b> on line <b>' . $trace[1]['line'] . '</b>');
        }
    }

}
