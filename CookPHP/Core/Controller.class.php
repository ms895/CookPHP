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

namespace Core;

/**
 * 控制器类
 * @author CookPHP <admin@cookphp.org>
 */
abstract class Controller extends View {

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
     * @param String $info 提示信息
     * @param boolean $status 返回状态
     */
    protected function ajaxReturn($info = '', int $status = 1, $data = '', $url = '') {
        $result = [];
        $result['status'] = (int) $status;
        if (is_string($info)) {
            $result['info'] = $this->language($info, null) ?: $info;
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

    /**
     * 直接输出json
     * @param mixed $value
     */
    protected function showJson($value) {
        header('Content-type:application/json;charset=' . CHARSET);
        exit(json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param string $msg 跳转提示信息
     */
    protected function redirect($url, $params = []) {
        Loader::redirect($this->url($url, $params));
    }

    /**
     * 实例model
     * @access protected
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    protected function model($table = null, $config = []) {
        return Loader::model($table, $config);
    }

    /**
     * 解析URL
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    protected function url(string $url, $params = [], $domain = false) {
        return Loader::url($url, $params, $domain);
    }

    /**
     * 返回语言
     * @access public
     * @param string $key
     * @return mixed
     */
    public function language($key, $default = null) {
        return Loader::language($key, $default);
    }

    /**
     * URl跳转
     * @param string $url
     */
    protected function header($url) {
        Loader::header($url);
    }

    /**
     * URl跳转 解析地址
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    protected function headerUrl(string $url, $params = [], $domain = false) {
        Loader::headerUrl($url, $params, $domain);
    }

    public function __destruct() {
        if (!IS_AJAX && DEBUG) {
            //Log::display();
        }
    }

}
