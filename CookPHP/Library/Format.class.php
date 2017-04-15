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
 * 格式化类
 * @author CookPHP <admin@cookphp.org>
 */
class Format {

    /**
     * 大小并格式化
     * @access public
     * @param int $size
     * @return string
     */
    public static function size($size): string {
        $size = (int) $size;
        $sizes = [' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
        return $size == 0 ? 0 : round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]; //('n/a') 
    }

    /**
     * 压缩css
     * @access public
     * @param string $content
     * @return string
     */
    public static function css(string &$content): string {
        $content = str_replace(["\r\n", "\r", "\n", "\t", "  ", "    ", "    "], '', preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $content));
        return $content;
    }

    /**
     * 压缩HTML
     * @access public
     * @param string $content
     * @return string
     */
    public static function html(string &$content): string {
        $content = preg_replace(['/\?><\?php/', '~>\s+<~', '~>(\s+\n|\r)~', "/> *([^ ]*) *</", "/[\s]+/", "/<!--[^!]*-->/", "/ \"/", "'/\*[^*]*\*/'"], ['', '><', '>', ">\\1<", ' ', '', "\"", ''], $content);
        return $content;
    }

}
