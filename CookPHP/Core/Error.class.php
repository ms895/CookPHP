<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core;

/**
 * Description of Error
 *
 * @author 费尔
 */
class Error {

    /**
     * 404错误处理
     * @param	string	$page		页面
     * @param 	bool	$logError	是否记录错误
     * @return	void
     */
    public static function show404($page = '', $logError = true) {
        Exception::show404($page, $logError);
    }

    /**
     * 错误处理程序
     * @param	int	$severity	日志级别
     * @param	string	$message	错误信息
     * @param	string	$filepath	文件路径
     * @param	int	$line		行
     * @return	void
     */
    public static function error($severity, $message, $filepath, $line) {
        $is_error = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);
        if ($is_error) {
            self::setStatusHeader(500);
        }
        if (($severity & error_reporting()) !== $severity) {
            return;
        }
        Exception::logException($severity, $message, $filepath, $line);
        if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
            Exception::showPHPError($severity, $message, $filepath, $line);
        }
        if ($is_error) {
            exit();
        }
    }

    /**
     * 异常处理
     * @param	Exception	$exception
     * @return	void
     */
    public static function exception($exception) {
        Exception::logException('error', 'Exception: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine());
        if (str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors'))) {
            Exception::showException($exception);
        }
        exit();
    }

    /**
     * 关闭处理程序
     * @return	void
     */
    public static function shutdown() {
        $last_error = error_get_last();
        if (isset($last_error) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
            self::error($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
        }
    }

    /**
     * Set HTTP Status Header
     *
     * @param	int	the status code
     * @param	string
     * @return	void
     */
    public static function setStatusHeader($code = 200, $text = '') {
        if (IS_CLI) {
            return;
        }
        if (empty($code) || !is_numeric($code)) {
            self::showError('Status codes must be numeric', 500);
        }
        if (empty($text)) {
            $code = (int) $code;
            $stati = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                422 => 'Unprocessable Entity',
                426 => 'Upgrade Required',
                428 => 'Precondition Required',
                429 => 'Too Many Requests',
                431 => 'Request Header Fields Too Large',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                511 => 'Network Authentication Required',
            );
            if (isset($stati[$code])) {
                $text = $stati[$code];
            } else {
                self::showError('No status text available. Please check your status code number or supply your own message text.', 500);
            }
        }

        if (IS_CGI) {
            header('Status: ' . $code . ' ' . $text, true);
        } else {
            $serverProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($serverProtocol . ' ' . $code . ' ' . $text, true, $code);
        }
    }

    /**
     * 显示错误
     * @param	string
     * @param	int
     * @param	string
     * @return	void
     */
    public static function showError($message, $status_code = 500, $heading = 'An Error Was Encountered') {
        $status_code = abs($status_code);
        if ($status_code < 100) {
            $exit_status = $status_code + 9;
            if ($exit_status > 125) {
                $exit_status = 1;
            }
            $status_code = 500;
        } else {
            $exit_status = 1;
        }

        Exception::showError($heading, $message, 'error_general', $status_code);
        exit();
    }

    /**
     * 显示错误
     * @param	string
     * @param	int
     * @param	string
     * @return	void
     */
    public static function showDbError($heading, $message='') {
        echo Exception::showError($heading, $message, 'error_db');
        exit();
    }

}
