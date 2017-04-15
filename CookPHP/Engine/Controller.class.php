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

use Library\Response;

/**
 * 控制器
 */
abstract class Controller extends Loader {

    protected $_vars = [];

    /**
     * 赋值
     * @param string|array $var
     * @param mixed $value
     */
    protected function assign($var, $value = null) {
        is_array($var) ? ($this->_vars = array_merge($this->_vars, $var)) : ($this->_vars[$var] = $value);
    }

    /**
     * 获取模板变量
     *
     * @param string $name
     * @return null|mixed
     */
    protected function getVar($name = '') {
        return $name === '' ? $this->_vars : ($this->_vars[$name] ?? null);
    }

    /**
     * 取得输出内容
     * @access protected
     * @param string $template 模板
     * @param string $id 识别ID
     * @return string
     */
    protected function fetch($template = null) {
        return $this->view()->render($template, $this->getVar());
    }

    /**
     * 显示输出内容
     * @access protected
     * @param string $template 模板
     * @param string $template
     */
    protected function display($template = '') {
        echo $this->fetch($template);
    }

    /**
     * 直接输出json
     * @param mixed $value
     */
    protected function showJson($value) {
        header('Content-type:application/json;charset=' . CHARSET);
        exit(json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     */
    protected function error($message, $url = '') {
        $this->ajaxReturn($message, 0, '', $url);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     */
    protected function success($message, $url = '') {
        $this->ajaxReturn($message, 1, '', $url);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param string $info 提示信息
     * @param boolean $status 返回状态
     */
    protected function ajaxReturn($info = '', int $status = 1, $data = '', $url = '') {
        $result = [];
        $result['status'] = (int) $status;
        if (is_string($info)) {
            $result['info'] = $info;
        }
        $result['url'] = $url;
        if (!empty($data)) {
            $result['data'] = $data;
        }
        if (is_array($info)) {
            $result['data'] = $info;
        }
        $this->showJson($result);
    }
    public function __set($name, $value) {
        $this->assign($name, $value);
    }
    public function __get($name) {
        return $this->getVar($name);
    }
}
