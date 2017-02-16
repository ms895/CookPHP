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
 * 视图类
 * @author CookPHP <admin@cookphp.org>
 */
class View extends Compile {

    public function __construct() {
        $this->init();
    }

    private function init() {
        PROJECT && $this->setTemplateDir(__VIEWS__ . PROJECT . DS);
        $this->setCacheDir(Config::get('view.cachedir'))
                ->setCompileDir(Config::get('view.compiledir'))
                ->setTemplateDir(__VIEWS__);
        foreach ((array) Config::get('view.config') as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __set($name, $value) {
        $this->assign($name, $value);
    }

    public function __get($name) {
        return $this->getVar($name);
    }

    /**
     * 解析模板名称
     * @access public
     * @param string $template
     * @return string
     */
    protected function replaceTemplate(&$template) {
        if (empty($template)) {
            $template = CONTROLLER . ':' . ACTION;
        } elseif (is_readable($template)) {
            return $template;
        } elseif (stristr($template, ':') === false) {
            $template = CONTROLLER . ':' . $template;
        }
        $template = str_ireplace(':', DS, trim($template, ':'));
        return $template;
    }

}
