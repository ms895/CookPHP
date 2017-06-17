<?php

if (!function_exists('is_alpha')) {

    /**
     * 验证是否是字母
     * @param string $string
     * @return bool
     */
    function is_alpha($string): bool {
        return (boolean) preg_match('/^[A-Za-z]+$/', $string);
    }

}
if (!function_exists('is_alpha_num')) {

    /**
     * 验证是否是字母和数字
     * @param string $string
     * @return bool
     */
    function is_alpha_num($string): bool {
        return (boolean) preg_match('/^[A-Za-z0-9]+$/', $string);
    }

}
if (!function_exists('is_alpha_dash')) {

    /**
     * 验证是否是字母、数字和下划线 破折号
     * @param string $string
     * @param int $min
     * @param int $max
     * @return bool
     */
    function is_alpha_dash($string, int $min = 0, int $max = 0): bool {
        return (boolean) preg_match('/^[A-Za-z0-9\-\_]' . (is_number_id($min) && is_number_id($max) ? '{' . $min . ',' . $max . '}' : '+') . '$/', $string);
    }

}
if (!function_exists('is_email')) {

    /**
     * 验证是否是有合法的email
     * @param string $email
     * @return bool
     */
    function is_email($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}
if (!function_exists('is_ip')) {

    /**
     * 验证是否是有合法的IP
     * @param string $ip
     * @return bool
     */
    function is_ip($ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
    }

}
if (!function_exists('is_ip4')) {

    /**
     * 验证是否是有合法的IP4
     * @param string $ip
     * @return bool
     */
    function is_ip4($ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

}
if (!function_exists('is_ip6')) {

    /**
     * 验证是否是有合法的IP6
     * @param string $ip
     * @return bool
     */
    function is_ip6($ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

}
if (!function_exists('is_ip_lan')) {

    /**
     * 验证是否是有合法的私域 IP （比如 192.168.0.1）
     * @param string $ip
     * @return bool
     */
    function is_ip_lan($ip): bool {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
    }

}
if (!function_exists('is_float')) {

    /**
     * 验证是否为浮点数
     * @param string $float
     * @return bool
     */
    function is_float($float): bool {
        return filter_var($float, FILTER_VALIDATE_FLOAT);
    }

}
if (!function_exists('is_number')) {

    /**
     * 验证是否为整数
     * @param string $number
     * @return bool
     */
    function is_number($number): bool {
        return filter_var($number, FILTER_VALIDATE_INT);
    }

}
if (!function_exists('is_number_id')) {

    /**
     * 验证是数字ID
     * @param int $number 需要被验证的数字
     * @return bool 如果大于等于0的整数数字返回true，否则返回false
     */
    function is_number_id($number): bool {
        return preg_match('/^[1-9][0-9]*$/i', $number);
    }

}
if (!function_exists('is_integer')) {

    /**
     * 验证是否为整数
     * @param string $number
     * @return bool
     */
    function is_integer($number): bool {
        return is_number($number);
    }

}
if (!function_exists('is_boolean')) {

    /**
     * 验证是否为布尔值
     * @param string $bool
     * @return bool
     */
    function is_boolean($bool): bool {
        return filter_var($bool, FILTER_VALIDATE_BOOLEAN);
    }

}
if (!function_exists('is_chinese')) {

    /**
     * 验证是否是中文
     * @param string $string 待验证的字串
     * @return bool 如果是中文则返回true，否则返回false
     */
    function is_chinese($string): bool {
        return (boolean) preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $string);
    }

}
if (!function_exists('is_html')) {

    /**
     * 验证是否是合法的html标记
     * @param string $string 待验证的字串
     * @return bool 如果是合法的html标记则返回true，否则返回false
     */
    function is_html($string): bool {
        return (boolean) preg_match('/^<(.*)>.*|<(.*)\/>$/', $string);
    }

}
if (!function_exists('is_script')) {

    /**
     * 验证是否是合法的客户端脚本
     * @param string $string 待验证的字串
     * @return bool 如果是合法的客户端脚本则返回true，否则返回false
     */
    function is_script($string): bool {
        return (boolean) preg_match('/<script(?:.*?)>(?:[^\x00]*?)<\/script>/', $string);
    }

}
if (!function_exists('is_mobile_phone')) {

    /**
     * 验证是否是大陆手机号码
     * @param string $phone 待验证的号码
     * @return bool
     */
    function is_mobile_phone($phone, $strlen = 11): bool {
        return strlen($phone) === intval($strlen) && preg_match('/^13[0-9]{1}\d{8}|14[57]{1}\d{8}|15[012356789]{1}\d{8}|17[0678]{1}\d{8}|18[0-9]{1}\d{8}$/', $phone);
    }

}
if (!function_exists('is_phone')) {

    /**
     * 检查电话或手机号码
     * @param  string    $number
     * @return void
     */
    function is_phone($number) {
        return is_tel($number) || is_mobile_phone($number);
    }

}
if (!function_exists('is_tel')) {

    /**
     * 检查电话号码
     * @param  int    $number
     * @return void
     */
    function is_tel($number) {
        return preg_match("/^([0-9]{3,4}-)?[0-9]{7,8}$/", $number);
    }

}
if (!function_exists('is_date')) {

    /**
     * 日期检查
     * @param  date $date
     * @return bool
     */
    function is_date($date) {
        return date('Y-m-d', strtotime($date)) === $date;
    }

}
if (!function_exists('is_date_time')) {

    /**
     * 日期时间检查
     * @param  date $date
     * @return bool
     */
    function is_date_time($date) {
        return date('Y-m-d H:i:s', strtotime($date)) === $date;
    }

}
if (!function_exists('is_required')) {

    /**
     * 验证是否是不能为空
     * @param mixed $value 待判断的数据
     * @return bool 如果为空则返回false,不为空返回true
     */
    function is_required($value): bool {
        return !empty($value);
    }

}
if (!function_exists('is_utf8')) {

    /**
     * 查字符串是否是UTF8编码
     * @param string $string 字符
     * @return bool
     */
    function is_utf8($string): bool {
        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($string[$i]);
            if ($c > 128) {
                if (($c >= 254)) {
                    return false;
                } elseif ($c >= 252) {
                    $bits = 6;
                } elseif ($c >= 248) {
                    $bits = 5;
                } elseif ($c >= 240) {
                    $bits = 4;
                } elseif ($c >= 224) {
                    $bits = 3;
                } elseif ($c >= 192) {
                    $bits = 2;
                } else {
                    return false;
                }
                if (($i + $bits) > $len) {
                    return false;
                }
                while ($bits > 1) {
                    $i++;
                    $b = ord($string[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }
                    $bits--;
                }
            }
        }
        return true;
    }

}
if (!function_exists('get_ip')) {

    /**
     * 返回客户端IP
     * @return string
     */
    function get_ip() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

}

if (!function_exists('parse_name')) {

    /**
     * 字符串命名风格转换
     * type false 将Java风格转换为C的风格 true 将C风格转换为Java的风格
     * @param string  $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    function parse_name($name, $type = false) {
        return $type ? ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                            return strtoupper($match[1]);
                        }, $name)) : strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

}

if (!function_exists('config')) {

    /**
     * 返回配制
     * @access public
     * @param string $key
     * @return mixed
     */
    function config($key, $default = null) {
        return \Library\Config::get($key, $default);
    }

}
if (!function_exists('language')) {

    /**
     * 返回语言
     * @access public
     * @param string $key
     * @return mixed
     */
    function language($key, $default = null) {
        return \Library\Language::get($key, $default);
    }

}
if (!function_exists('url')) {

    /**
     * 解析URL
     * @param string $url
     * @param array $params
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    function url(string $url, $params = [], bool $domain = false) {
        return \Library\Url::parse($url, $params, $domain);
    }

}
if (!function_exists('url_base')) {

    /**
     * 解析URL
     * @param string $url
     * @param bool $domain 是否显示域名和协议
     * @return string
     */
    function url_base(string $url, bool $domain = false) {
        return \Library\Url::base($url, $domain);
    }

}

if (!function_exists('view')) {

    /**
     * 初始视图
     * @return View
     */
    function view() {
        static $view = null;
        return $view ?: $view = new \Engine\View();
    }

}
if (!function_exists('model')) {

    /**
     * 实例model
     * @access protected
     * @param string|null $table 表
     * @param array $config 配制
     * @return \Core\Model
     */
    function model(string $table = null, $config = []) {
        static $_model = [];
        if (!isset($_model[$table])) {
            if (!empty($table) && class_exists(($newtable = '\\Model\\' . parse_name(preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $table), true)))) {
                $_model[$table] = new $newtable(null, $config);
            } else {
                $_model[$table] = new \Engine\Model($table ?: null, $config);
            }
        }

        return $_model[$table];
    }

}

if (!function_exists('redirect')) {

    /**
     * 重定向
     * @param string $url
     * @param bool $parse
     * @param int $status
     */
    function redirect(string $url, bool $parse = true, int $status = 302) {
        exit(header('Location: ' . ($parse ? url($url) : str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url)), true, $status));
    }

}