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

use Library\Mailer\Message;
use Library\Mailer\SMTP;

/**
 * 邮箱发送
  $ok = (new Mailer())
  ->setServer('smtp.server.com', 465)
  ->setAuth('tom@server.com', 'password','ssl')
  ->setFrom('Tom', 'tom@server.com')
  ->setFakeFrom('Obama', 'fake@address.com') // if u want, a fake name, a fake email
  ->addTo('Jerry', 'jerry@server.com')
  ->setSubject('Hello')
  ->setBody('Hi, Jerry! I <strong>love</strong> you.')
  ->addAttachment('host', '/etc/hosts')
  ->send();
  var_dump($ok);
 *
 * @package Tx
 */
class Mailer {

    /**
     * SMTP Class
     * @var SMTP
     */
    protected $smtp;

    /**
     * Mail Message
     * @var Message
     */
    protected $message;

    /**
     * construct function
     */
    public function __construct() {
        $this->smtp = new SMTP();
        $this->message = new Message();
    }

    /**
     * 连接服务器
     * @param string $host server
     * @param int $port port
     * @param string $secure ssl tls
     * @return $this
     */
    public function setServer($host, $port, $secure = null) {
        $this->smtp->setServer($host, $port, $secure);
        return $this;
    }

    /**
     * 设置认证账号和密码
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth($username, $password) {
        $this->smtp->setAuth($username, $password);
        return $this;
    }

    /**
     * auth oauthbearer with server
     * @param string $accessToken
     * @return $this
     */
    public function setOAuth($accessToken) {
        $this->smtp->setOAuth($accessToken);
        return $this;
    }

    /**
     * 设置发件人
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email) {
        $this->message->setFrom($name, $email);
        return $this;
    }

    /**
     * 代发件人
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFakeFrom($name, $email) {
        $this->message->setFakeFrom($name, $email);
        return $this;
    }

    /**
     * 设置收件我
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setTo($name, $email) {
        $this->message->addTo($name, $email);
        return $this;
    }

    /**
     * 添加收件人
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addTo($name, $email) {
        $this->message->addTo($name, $email);
        return $this;
    }

    /**
     * 添加抄送
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addCc($name, $email) {
        $this->message->addCc($name, $email);
        return $this;
    }

    /**
     * 密件抄送
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addBcc($name, $email) {
        $this->message->addBcc($name, $email);
        return $this;
    }

    /**
     * 设置邮件主题
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->message->setSubject($subject);
        return $this;
    }

    /**
     * 设置邮件正文
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        $this->message->setBody($body);
        return $this;
    }

    /**
     * 设置邮件附件
     * @param $name
     * @param $path
     * @return $this
     */
    public function setAttachment($name, $path) {
        $this->message->addAttachment($name, $path);
        return $this;
    }

    /**
     * 添加邮件附件
     * @param $name
     * @param $path
     * @return $this
     */
    public function addAttachment($name, $path) {
        $this->message->addAttachment($name, $path);
        return $this;
    }

    /**
     *  发送邮件
     * @return boolean
     */
    public function send() {
        return $this->smtp->send($this->message);
    }

}
