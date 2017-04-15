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
 * 注册
 */
final class Registry {

    private $class = [];
    private $className = [
        'Action' => '\Base\Action',
        'Controller' => '\Base\Controller',
        'Event' => '\Base\Event',
        'Front' => '\Base\Front',
        'Loader' => '\Base\Loader',
        'Model' => '\Base\Model',
        'Proxy' => '\Base\Proxy',
        'Config' => '\Library\Config'
    ];

    public function get($key) {
        if (!$this->has($key) && isset($this->className[$name = ucfirst($key)])) {
            $this->class[strtolower($key)] = new $this->className[$name];
            echo $key . PHP_EOL;
        }
        return $this->class[$key] ?? null;
    }

    public function set($key, $value) {
        $this->class[strtolower($key)] = $value;
        return $this;
    }

    public function has($key) {
        return isset($this->class[$key]);
    }

    public function __get($key) {
        return $this->get($key);
    }

    public function __set($key, $value) {
        $this->set($key, $value);
    }

}
