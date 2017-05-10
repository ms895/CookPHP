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
 * 加密
 * @author CookPHP <admin@cookphp.org>
 */
final class Encryption {

    /**
     * 加密
     * @param string $value
     * @return string
     */
    public static function encrypt(string $value, string $key = ''): string {
        return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $key ?: self::getKey(), true), $value, MCRYPT_MODE_ECB)), '+/=', '-_,');
    }

    /**
     * 解密
     * @param string $value
     * @return string
     */
    public static function decrypt(string $value, string $key = ''): string {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', $key ?: self::getKey(), true), base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_ECB));
    }

    /**
     * 加密
     * @access public
     * @param string $string 字符
     * @param string $key
     * @param int $expiry
     * @return string
     */
    public static function encode(string $string, string $key = '', int $expiry = 0): string {
        return self::authcode($string, 'ENCODE', $key, $expiry);
    }

    /**
     * 解密
     * @access public
     * @param string $string 字符
     * @param string $key
     * @param int $expiry
     * @return string
     */
    public static function decode(string $string, string $key = '', int $expiry = 0): string {
        return self::authcode($string, 'DECODE', $key, $expiry);
    }

    /**
     * 加密和解密
     * @link http://www.comsenz.com/
     * @access public
     * @param string $string 字符
     * @param string $operation 加密(ENCODE)或解密(DECODE)
     * @param string $key
     * @param int $expiry
     * @return string
     */
    public static function authcode(string $string, string $operation = 'DECODE', string $key = '', int $expiry = 0): string {
        $ckey_length = 4;
        $key = md5($key ?: self::getKey());
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        return $operation == 'DECODE' ? ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16) ? substr($result, 26) : '') : ($keyc . str_replace('=', '', base64_encode($result)));
    }

    /**
     * 返回密码字符
     * @return string
     */
    public static function getKey() {
        return Cache::remember('CookPHPEncryptionKey', function() {
                    return hash('sha256', uniqid('', true), true);
                }, 0, 'File');
    }

}