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

namespace Library;

use Engine\Route;

/**
 * URL
 * @author CookPHP <admin@cookphp.org>
 */
class Url {

    /**
     * 解析URL
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    public static function parse(string $url, $params = [], $domain = false): string {
        if (self::isUrl($url)) {
            return $url;
        }
        $array = parse_url($url);
        if (isset($array['fragment'])) {
            $anchor = $array['fragment'];
            if (false !== strpos($anchor, '?')) {
                list($anchor, $array['query']) = explode('?', $anchor, 2);
            }
            if (false !== strpos($anchor, '@')) {
                list($anchor, $hosts) = explode('@', $anchor, 2);
            }
        }

        if (substr($url, 0, 1) === '/') {
            $array['path'] = $url;
        }
        if (empty($array['path']) || strpos($array['path'], '/') === false) {
            $array['path'] = Route::getController() . '/' . (!empty($array['path']) ? $array['path'] : Route::getMethod()) . (Config::get('route.htmlsuffix') ? '.' . Config::get('route.htmlsuffix') : '');
        }

        $url = !empty($array['path']) ? $array['path'] : Route::getController() . '/' . Route::getMethod() . (Config::get('route.htmlsuffix') ? '.' . Config::get('route.htmlsuffix') : '');

        $host = self::host();
        if (isset($array['scheme'])) {
            if (Config::get('route.domain')) {
                $routeHost = $host === 'localhost' ? 'localhost' : strtolower($array['scheme']) . strstr($host, '.');
            } else {
                $url = ucfirst(strtolower($array['scheme'])) . '/' . $url;
            }
        } else {
            if (!Config::get('route.domain')) {
                if (substr($url, 0, 1) !== '/') {
                    $url = (strtolower(Config::get('route.project')) === strtolower(Route::getRoute()) ? '' : Route::getRoute() . '/') . $url;
                }
            }
        }


        if (isset($array['query']) || !empty($params)) {
            !empty($array['query']) && parse_str($array['query'], $query);
            $params = !empty($query) ? array_merge($query, $params) : $params;
            if (!empty($params)) {
                $str = '/';
                $depr = '/';
                foreach ($params as $var => $val) {
                    $str .= $var . $depr . $val . $depr;
                }
                $url .= substr($str, 0, -1);
            }
        }

        $url = (REWRITE ? BASEROOT : BASEFILE ) . '/' . trim($url, '/');
        if (isset($anchor)) {
            $url .= '#' . $anchor;
        }
        if (isset($routeHost)) {
            $url = self::scheme() . '//' . $domain . $url;
        } else {
            $url = ($domain ? self::domain() : '') . $url;
        }
        return $url;
    }

    /**
     * 返回完整URL 不含 BASEFILE和后缀
     * @param string $url
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    public static function base(string $url, $domain = false): string {
        if (self::isUrl($url)) {
            return $url;
        }
        $url = BASEROOT . '/' . ltrim($url, '/');
        $url = ($domain ? self::domain() : '') . $url;
        return $url;
    }

    /**
     * 获取当前包含协议的域名
     * @access public
     * @param string $auto 是否自动协议
     * @return string
     */
    public static function domain($auto = false): string {
        return ($auto ? '//' : self::scheme() . '://') . self::host();
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public static function scheme(): string {
        return self::isSsl() ? 'https' : 'http';
    }

    /**
     * 当前请求URL地址中的query参数
     * @access public
     * @return string
     */
    public static function query() {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    /**
     * 当前请求的host
     * @access public
     * @return string
     */
    public static function host() {
        return strtolower($_SERVER['HTTP_HOST']);
    }

    /**
     * 当前请求URL地址中的port参数
     * @access public
     * @return integer
     */
    public static function port() {
        return $_SERVER['SERVER_PORT'] ?? 80;
    }

    /**
     * 当前请求 SERVER_PROTOCOL
     * @access public
     * @return integer
     */
    public static function protocol() {
        return $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    }

    /**
     * 当前请求 REMOTE_PORT
     * 连接到服务器时所使用的端口
     * @access public
     * @return integer
     */
    public static function remotePort() {
        return $_SERVER['REMOTE_PORT'];
    }

    /**
     * 返回客户端的HTTP
     * @access public
     * @return string
     */
    public static function getHttpVersion() {
        static $_httpVersion = null;
        return $_httpVersion ?: ($_httpVersion = isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0' ? '1.0' : '1.1');
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public static function isSsl(): bool {
        return (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) || (isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']) || (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) ? true : false;
    }

    /**
     * 检测是否为完整url
     * @param string $url
     * @return bool
     */
    public static function isUrl($url): bool {
        return (bool) preg_match('/^(?:http(?:s)?:\/\/(?:[\w-]+\.)+[\w-]+(?:\:\d+)*+(?:\/[\w- .\/?%&=]*)?)$/', $url);
    }

}
