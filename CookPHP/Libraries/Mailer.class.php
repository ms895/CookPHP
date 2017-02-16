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

namespace Libraries;

require_once __DIR__ . DS . 'PHPMailer' . DS . 'PHPMailerAutoload.php';

use PHPMailer;
use Core\Config;

/**
 * 邮箱类
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author 费尔
 */
class Mailer extends PHPMailer {

    public function __construct($exceptions = null) {
        parent::__construct($exceptions);
        $this->Host = Config::get('email.server');
        $this->SMTPAuth = true;
        $this->Username = Config::get('email.username');
        $this->Password = Config::get('email.password');
        $this->SMTPSecure = Config::get('email.ssh') ? 'ssl' : 'tls';
        $this->Port = Config::get('email.port');
    }

}
