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
 * 模板类
 * @author CookPHP <admin@cookphp.org>
 */
class Template {

    private $adaptor;

    public function __construct($adaptor = null) {
        $class = '\\Library\\Template\\' . ucwords($adaptor ?: Config::get('template.driver'));
        if (class_exists($class)) {
            $this->adaptor = new $class();
        } else {
            throw new \Exception('Error: Could not load template adaptor ' . $adaptor . '!');
        }
    }

    /**
     * 渲染模板
     * @access protected
     * @param string $template 模板
     * @param mixed $data 赋值
     */
    public function render($template = null, $data = null) {
        return $this->adaptor->render($template, $data);
    }


}
