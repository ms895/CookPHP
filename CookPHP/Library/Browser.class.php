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
 * 浏览器
 * @author CookPHP <admin@cookphp.org>
 */
class Browser {

    /**
     * 获取浏览器相关参数
     * @return string
     */
    public static function getUA() {
        return USER_AGENT;
    }

    /**
     * 判断是否来自搜索引擎蜘蛛爬虫
     * 百度蜘蛛、google蜘蛛、soso搜搜蜘蛛、360蜘蛛、有道搜索引擎蜘蛛……
     * @return boolean
     */
    public static function isSpider() {
        return (boolean) preg_match('/(baiduspider|googlebot|sosospider|360spider|HaoSouSpider|slurp|yodaobot|sogou|msnbot|bingbot)/i', self::getUA());
    }

    /**
     * 检测是否为AJAX请求
     * @return boolean
     */
    public static function isAJAX() {
        return IS_AJAX;
    }

    /**
     * 检测是否为手机浏览
     * @return boolean
     */
    public static function isPhone() {
        return (boolean) preg_match('/(iPhone|iPod|Android|iOS|iPad|Backerry|WebOS|Symbian|Windows Phone|Phone)/i', self::getUA());
    }

    /**
     * 检测是否为微信浏览
     * @return boolean
     */
    public static function isWeChat() {
        return self::isPhone() && preg_match('/MicroMessenger/i', self::getUA());
    }

    /**
     * 检测是否为QQ浏览
     * @return boolean
     */
    public static function isQQ() {
        return self::isPhone() && preg_match('/MQQBrowser/i', self::getUA());
    }

    /**
     * 检测是否为支付宝浏览
     * @return boolean
     */
    public static function isAlipay() {
        return self::isPhone() && preg_match('/AlipayClient/i', self::getUA());
    }

    /**
     * 检测是否为iPhone浏览
     * @return boolean
     */
    public static function isiPhoneOS() {
        return self::isPhone() && self::parseUserAgent()['platform'] === 'iPhone';
    }

    /**
     * 检测是否为Android浏览
     * @return boolean
     */
    public static function isAndroidOS() {
        return self::isPhone() && self::parseUserAgent()['platform'] === 'Android';
    }

    /**
     * 检测是否为Linux浏览
     * @return boolean
     */
    public static function isLinuxOS() {
        return self::parseUserAgent()['platform'] === 'Linux';
    }

    /**
     * 检测是否为Windows浏览
     * @return boolean
     */
    public static function isWindowsOS() {
        return self::parseUserAgent()['platform'] === 'Windows';
    }

    /**
     * 解析用户浏览器信息
     * @param string $ua
     * @return array
     * Array
     *  (
     *  [platform] => iPhone
     *  [browser] => Safari
     *  [version] => 9.0
     *  )
     * @link https://github.com/donatj/PhpUserAgent
     * @throws \Exception
     */
    public static function parseUserAgent($ua = USER_AGENT) {
        $platform = null;
        $browser = null;
        $version = null;
        $empty = ['platform' => $platform, 'browser' => $browser, 'version' => $version];
        if (!$ua) {
            return $empty;
        }
        if (preg_match('/\((.*?)\)/im', $ua, $parent_matches)) {
            preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);
            $priority = array('Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'CrOS', 'X11');
            $result['platform'] = array_unique($result['platform']);
            if (count($result['platform']) > 1) {
                if (($keys = array_intersect($priority, $result['platform']))) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif (isset($result['platform'][0])) {
                $platform = $result['platform'][0];
            }
        }
        if ($platform == 'linux-gnu' || $platform == 'X11') {
            $platform = 'Linux';
        } elseif ($platform == 'CrOS') {
            $platform = 'Chrome OS';
        }
        preg_match_all('%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
				TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|
				Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
				Valve\ Steam\ Tenfoot|
				NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix', $ua, $result, PREG_PATTERN_ORDER);
        if (!isset($result['browser'][0]) || !isset($result['version'][0])) {
            if (preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $ua, $result)) {
                return ['platform' => $platform ?: null, 'browser' => $result['browser'], 'version' => isset($result['version']) ? $result['version'] ?: null : null];
            }

            return $empty;
        }
        if (preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $ua, $rv_result)) {
            $rv_result = $rv_result['version'];
        }
        $browser = $result['browser'][0];
        $version = $result['version'][0];
        $lowerBrowser = array_map('strtolower', $result['browser']);
        $find = function ( $search, &$key, &$value = null ) use ( $lowerBrowser ) {
            $search = (array) $search;
            foreach ($search as $val) {
                $xkey = array_search(strtolower($val), $lowerBrowser);
                if ($xkey !== false) {
                    $value = $val;
                    $key = $xkey;
                    return true;
                }
            }
            return false;
        };
        $key = 0;
        $val = '';
        if ($browser == 'Iceweasel' || strtolower($browser) == 'icecat') {
            $browser = 'Firefox';
        } elseif ($find('Playstation Vita', $key)) {
            $platform = 'PlayStation Vita';
            $browser = 'Browser';
        } elseif ($find(['Kindle Fire', 'Silk'], $key, $val)) {
            $browser = $val == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif ($find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif ($find('Kindle', $key, $platform)) {
            $browser = $result['browser'][$key];
            $version = $result['version'][$key];
        } elseif ($find('OPR', $key)) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif ($find('Opera', $key, $browser)) {
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($find('Puffin', $key, $browser)) {
            $version = $result['version'][$key];
            if (strlen($version) > 3) {
                $part = substr($version, -2);
                if (ctype_upper($part)) {
                    $version = substr($version, 0, -2);
                    $flags = ['IP' => 'iPhone', 'IT' => 'iPad', 'AP' => 'Android', 'AT' => 'Android', 'WP' => 'Windows Phone', 'WT' => 'Windows'];
                    if (isset($flags[$part])) {
                        $platform = $flags[$part];
                    }
                }
            }
        } elseif ($find(['IEMobile', 'Edge', 'Midori', 'Vivaldi', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome'], $key, $browser)) {
            $version = $result['version'][$key];
        } elseif ($rv_result && $find('Trident', $key)) {
            $browser = 'MSIE';
            $version = $rv_result;
        } elseif ($find('UCBrowser', $key)) {
            $browser = 'UC Browser';
            $version = $result['version'][$key];
        } elseif ($find('CriOS', $key)) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif ($browser == 'AppleWebKit') {
            if ($platform == 'Android' && !($key = 0)) {
                $browser = 'Android Browser';
            } elseif (strpos($platform, 'BB') === 0) {
                $browser = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } else {
                $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
            }
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($pKey = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser']))) {
            $pKey = reset($pKey);
            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
            $browser = 'NetFront';
        }
        return ['platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null];
    }

}
