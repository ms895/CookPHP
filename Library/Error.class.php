<?php

/**
 * CookPHP framework
 *
 * @name CookPHP framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link http://www.cookphp.org
 * @copyright cookphp.org
 * @license <a href='http://www.cookphp.org'>CookPHP</a>
 */

namespace Library;

/**
 * 错误类
 * @author CookPHP <admin@cookphp.org>
 */
class Error {

    /**
     * 错误输出
     * @param string $message
     * @param string $sql
     * @return void
     */
    public static function db($message, $sql) {
        self::httpStatus(500);
        self::requireFile('db', $message, $sql);
        exit;
    }

    /**
     * 输出404
     */
    public static function notFound() {
        self::httpStatus(404);
        self::requireFile(404);
        exit;
    }

    /**
     * 错误输出
     * @param array|string $error 错误
     * @return void
     */
    public static function show($error) {
        if (!DEBUG) {
            self::httpStatus(404);
        } else {
            print_r($error);
        }

        exit;
    }

    private static function requireFile($filename = 'php', $heading = null, $message = null) {
        if (is_file(__ERROR__ . $filename . '.php')) {
            require __ERROR__ . $filename . '.php';
        } else {
            require __DIR__ . DS . 'Error' . DS . $filename . '.php';
        }
    }

    /**
     * 发送HTTP状态
     * @param integer $code 状态码
     * @return void
     */
    public static function httpStatus($code) {
        static $_status = [
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
            302 => 'Moved Temporarily ',
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
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        ];
        if (isset($_status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
            header('Status:' . $code . ' ' . $_status[$code]);
        }
    }

}
