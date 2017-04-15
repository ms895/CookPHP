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

namespace Library\Template;

use Library\Config;

/**
 * CookPHP模板引擎
 */
final class Cook extends \Library\Template\Cook\Cook {

    public function __construct() {
        $this->init();
    }

    private function init() {
        APP_ROUTE ? $this->setTemplateDir(__VIEWS__ . APP_ROUTE . DS) : $this->setTemplateDir(__VIEWS__);
        $this->setCacheDir(Config::get('template.cachedir'))->setCompileDir(Config::get('template.compiledir'));
        foreach ((array) Config::get('template.config') as $key => $value) {
            $this->$key = $value;
        }
    }

}
