<?php

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
if (!function_exists('library')) {

    /**
     * 初始Library
     * @access protected
     * @param string $route
     * @return library
     * @throws \Exception
     */
    function library(string $route) {
        static $library = [];
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $route);
        if (!isset($library[$route])) {
            try {
                $class = '\\Library\\' . $route;
                $library[$route] = new $class;
            } catch (\Exception $e) {
                throw new \Exception('Error: Could not load library ' . $route . '!');
            }
        }
        return $library[$route];
    }

}
if (!function_exists('helper')) {

    /**
     * 加载帮助函数
     * @param string $route
     * @throws \Exception
     */
    function helper(string $route) {
        if (file_exists($file = __COOK__ . 'Helper' . DS . preg_replace('/[^a-zA-Z0-9_\/]/', '', (string) $route) . '.php')) {
            include_once($file);
        } else {
            throw new \Exception('Error: Could not load helper ' . $route . '!');
        }
    }

}
if (!function_exists('cache')) {

    /**
     * 初始缓存
     * @param string $adaptor
     * @param int $expire
     * @return Cache
     */
    function cache(string $adaptor = null, int $expire = 3600) {
        return new \Library\Cache($adaptor, $expire);
    }

}
if (!function_exists('view')) {

    /**
     * 初始缓存
     * @param string $adaptor
     * @param int $expire
     * @return Cache
     */
    function view(string $adaptor = null) {
        static $view = null;
        return $view ?: $view = new \Library\Template($adaptor ?: config('template.driver'));
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
                $_model[$table] = new \Library\Model($table ?: null, $config);
            }
        }

        return $_model[$table];
    }

}
if (!function_exists('db')) {

    function db($options = null) {
        static $_db = [];
        $key = implode(',', $options);
        if (!isset($_db[$key])) {
            $_db[$key] = new \Library\Db($options);
        }
        return $_db[$key];
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
if (!function_exists('is_alpha')) {

    /**
     * 验证是否是字母
     * @access public
     * @param string $string
     * @return bool
     */
    function is_alpha($string): bool {
        return \Library\Validate::isAlpha($string);
    }

}
if (!function_exists('is_alpha_num')) {

    /**
     * 验证是否是字母和数字
     * @access public
     * @param string $string
     * @return bool
     */
    function is_alpha_num($string): bool {
        return \Library\Validate::isAlphaNum($string);
    }

}
if (!function_exists('is_alpha_dash')) {

    /**
     * 验证是否是字母、数字和下划线 破折号
     * @access public
     * @param string $string
     * @param int $min
     * @param int $max
     * @return bool
     */
    function is_alpha_dash($string, int $min = 0, int $max = 0): bool {
        return \Library\Validate::isAlphaDash($string, $min, $max);
    }

}
if (!function_exists('is_email')) {

    /**
     * 验证是否是有合法的email
     * @access public
     * @param string $email
     * @return bool
     */
    function is_email($email): bool {
        return \Library\Validate::isEmail($email);
    }

}
if (!function_exists('is_ip')) {

    /**
     * 验证是否是有合法的IP
     * @access public
     * @param string $ip
     * @return bool
     */
    function is_ip($ip): bool {
        return \Library\Validate::isIP($ip);
    }

}
if (!function_exists('is_ip4')) {

    /**
     * 验证是否是有合法的IP4
     * @access public
     * @param string $ip
     * @return bool
     */
    function is_ip4($ip): bool {
        return \Library\Validate::isIP4($ip);
    }

}
if (!function_exists('is_ip6')) {

    /**
     * 验证是否是有合法的IP6
     * @access public
     * @param string $ip
     * @return bool
     */
    function is_ip6($ip): bool {
        return \Library\Validate::isIP6($ip);
    }

}
if (!function_exists('is_ip_lan')) {

    /**
     * 验证是否是有合法的私域 IP （比如 192.168.0.1）
     * @access public
     * @param string $ip
     * @return bool
     */
    function is_ip_lan($ip): bool {
        return \Library\Validate::isIPLAN($ip);
    }

}
if (!function_exists('is_float')) {

    /**
     * 验证是否为浮点数
     * @access public
     * @param string $float
     * @return bool
     */
    function is_float($float): bool {
        return \Library\Validate::isFloat($float);
    }

}
if (!function_exists('is_number')) {

    /**
     * 验证是否为整数
     * @access public
     * @param string $number
     * @return bool
     */
    function is_number($number): bool {
        return \Library\Validate::isNumber($number);
    }

}
if (!function_exists('is_number_id')) {

    /**
     * 验证是数字ID
     * @access public
     * @param int $number 需要被验证的数字
     * @return bool 如果大于等于0的整数数字返回true，否则返回false
     */
    function is_number_id($number): bool {
        return \Library\Validate::isNumberId($number);
    }

}
if (!function_exists('is_integer')) {

    /**
     * 验证是否为整数
     * @access public
     * @param string $number
     * @return bool
     */
    function is_integer($number): bool {
        return \Library\Validate::isInteger($number);
    }

}
if (!function_exists('is_boolean')) {

    /**
     * 验证是否为布尔值
     * @access public
     * @param string $bool
     * @return bool
     */
    function is_boolean($bool): bool {
        return \Library\Validate::isBoolean($bool);
    }

}
if (!function_exists('is_chinese')) {

    /**
     * 验证是否是中文
     * @access public
     * @param string $string 待验证的字串
     * @return bool 如果是中文则返回true，否则返回false
     */
    function is_chinese($string): bool {
        return \Library\Validate::isChinese($string);
    }

}
if (!function_exists('is_html')) {

    /**
     * 验证是否是合法的html标记
     * @access public
     * @param string $string 待验证的字串
     * @return bool 如果是合法的html标记则返回true，否则返回false
     */
    function is_html($string): bool {
        return \Library\Validate::isHtml($string);
    }

}
if (!function_exists('is_script')) {

    /**
     * 验证是否是合法的客户端脚本
     * @access public
     * @param string $string 待验证的字串
     * @return bool 如果是合法的客户端脚本则返回true，否则返回false
     */
    function is_script($string): bool {
        return \Library\Validate::isScript($string);
    }

}
if (!function_exists('is_mobilephone')) {

    /**
     * 验证是否是大陆手机号码
     * @access public
     * @param string $phone 待验证的号码
     * @return bool
     */
    function is_mobilephone($phone): bool {
        return \Library\Validate::isMobilephone($phone);
    }

}
if (!function_exists('is_phone')) {

    /**
     * 检查电话或手机号码
     * @param  string    $number 
     * @static
     * @access public
     * @return void
     */
    function is_phone($number) {
        return \Library\Validate::isPhone($number);
    }

}
if (!function_exists('is_tel')) {

    /**
     * 检查电话号码
     * @param  int    $number 
     * @static
     * @access public
     * @return void
     */
    function is_tel($number) {
        return \Library\Validate::isTel($number);
    }

}
if (!function_exists('is_date')) {

    /**
     * 日期检查
     * @param  date $date 
     * @static
     * @access public
     * @return bool
     */
    function is_date($date) {
        return \Library\Validate::isDate($date);
    }

}
if (!function_exists('is_date_time')) {

    /**
     * 日期时间检查
     * @param  date $date 
     * @static
     * @access public
     * @return bool
     */
    function is_date_time($date) {
        return \Library\Validate::isDateTime($date);
    }

}
if (!function_exists('is_required')) {

    /**
     * 验证是否是不能为空
     * @access public
     * @param mixed $value 待判断的数据
     * @return bool 如果为空则返回false,不为空返回true
     */
    function is_required($value): bool {
        return \Library\Validate::isRequired($value);
    }

}
if (!function_exists('is_utf8')) {

    /**
     * 查字符串是否是UTF8编码
     * @access public
     * @param string $string 字符
     * @return bool
     */
    function is_utf8($string): bool {
        return \Library\Validate::isUtf8($string);
    }

}