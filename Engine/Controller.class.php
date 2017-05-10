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
 * 控制器
 */
abstract class Controller extends Loader {

    /**
     * 渲染模板
     * @access public
     * @param string $template 模板
     * @param mixed $data 赋值
     * @param bool $return 是否直接返回，默认false输出
     */
    protected function render($template = null, $data = null, $return = false) {
        !empty($data) && $this->assign($data);
        $content = $this->fetch($template);
        if ($return) {
            return $content;
        } else {
            echo $content;
        }
    }

    /**
     * 赋值
     * @param string|array $var
     * @param mixed $value
     */
    protected function assign($var, $value = null) {
        $this->view()->assign($var, $value);
    }

    /**
     * 获取模板变量
     *
     * @param string $name
     * @return null|mixed
     */
    protected function getVar($name = '') {
        return $this->view()->getVar($name);
    }

    /**
     * 取得输出内容
     * @access protected
     * @param string $template 模板
     * @param string $id 识别ID
     * @return string
     */
    protected function fetch($template = null) {
        return $this->view()->fetch($template);
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
     * 操作成功
     * @access protected
     * @param string $message 信息
     */
    protected function success($message, $url = '') {
        $this->ajaxReturn($message, 'success', '', $url);
    }

    /**
     * 操作一般信息
     * @access protected
     * @param string $message 信息
     */
    protected function info($message, $url = '') {
        $this->ajaxReturn($message, 'info', '', $url);
    }

    /**
     * 操作失败
     * @access protected
     * @param string $message 信息
     */
    protected function error($message, $url = '') {
        $this->ajaxReturn($message, 'error', '', $url);
    }

    /**
     * 操作警告
     * @access protected
     * @param string $message 信息
     */
    protected function warning($message, $url = '') {
        $this->ajaxReturn($message, 'warning', '', $url);
    }

    /**
     * 操作危险
     * @access protected
     * @param string $message 信息
     */
    protected function danger($message, $url = '') {
        $this->ajaxReturn($message, 'danger', '', $url);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param string $info 提示信息
     * @param boolean $type 消息类型
     */
    protected function ajaxReturn($info = '', $type = 'info', $data = '', $url = '') {
        $result = [];
        $result['type'] = $type;
        if (is_string($info)) {
            $result['message'] = $this->language($info) ?: $info;
        } elseif (is_array($info)) {
            $result['data'] = $info;
        }
        $result['url'] = $url;
        if (!empty($data)) {
            $result['data'] = $data;
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
