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

/**
 * 原生PHP
 */
final class Php {

    public function render($template, $data = null) {
        $this->replaceTemplate($template);
        if (is_file($template)) {
            extract($data);
            unset($data);
            ob_start();
            require($template);
            return ob_get_clean();
        }
        exit(trigger_error('Error: Could not load template ' . $template . '!'));
    }

    /**
     * 解析模板名称
     * @access protected
     * @param string $template
     * @return string
     */
    private function replaceTemplate(&$template) {
        if (empty($template)) {
            $template = APP_CONTROLLER . ':' . APP_METHOD;
        } elseif (is_readable($template)) {
            return $template;
        } elseif (stristr($template, ':') === false) {
            $template = APP_CONTROLLER . ':' . $template;
        }
        $template = str_ireplace(':', DS, trim($template, ':'));
        $template = APP_ROUTE ? __VIEWS__ . APP_ROUTE . DS . $template . '.php' : __VIEWS__ . $template . '.php';
    }

}
