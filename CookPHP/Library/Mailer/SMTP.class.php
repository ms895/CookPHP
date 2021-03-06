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

namespace Library\Mailer;

use \Exception;

class SMTP {

    /**
     * smtp socket
     */
    protected $smtp;

    /**
     * smtp server
     */
    protected $host;

    /**
     * smtp server port
     */
    protected $port;

    /**
     * smtp secure ssl tls
     */
    protected $secure;

    /**
     * EHLO message
     */
    protected $ehlo;

    /**
     * smtp username
     */
    protected $username;

    /**
     * smtp password
     */
    protected $password;

    /**
     * oauth access token
     */
    protected $oauthToken;

    /**
     * $this->CRLF
     * @var string
     */
    protected $CRLF = "\r\n";

    /**
     * @var Message
     */
    protected $message;

    /**
     * Stack of all commands issued to SMTP
     * @var array
     */
    protected $commandStack = array();

    /**
     * Stack of all results issued to SMTP
     * @var array
     */
    protected $resultStack = array();

    /**
     * set server and port
     * @param string $host server
     * @param int $port port
     * @param string $secure ssl tls
     * @return $this
     */
    public function setServer($host, $port, $secure = null) {
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;
        if (!$this->ehlo)
            $this->ehlo = $host;
        return $this;
    }

    /**
     * auth login with server
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth($username, $password) {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * auth oauthbearer with server
     * @param string $accessToken
     * @return $this
     */
    public function setOAuth($accessToken) {
        $this->oauthToken = $accessToken;
        return $this;
    }

    /**
     * set the EHLO message
     * @param $ehlo
     * @return $this
     */
    public function setEhlo($ehlo) {
        $this->ehlo = $ehlo;
        return $this;
    }

    /**
     * Send the message
     *
     * @param Message $message
     * @return bool
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function send(Message $message) {
        $this->message = $message;
        $this->connect()
                ->ehlo();

        if ($this->secure === 'tls') {
            $this->starttls()
                    ->ehlo();
        }
        if ($this->username !== null || $this->password !== null) {
            $this->authLogin();
        } elseif ($this->oauthToken !== null) {
            $this->authOAuthBearer();
        }
        $this->mailFrom()
                ->rcptTo()
                ->data()
                ->quit();
        return fclose($this->smtp);
    }

    /**
     * connect the server
     * SUCCESS 220
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function connect() {
        $host = ($this->secure == 'ssl') ? 'ssl://' . $this->host : $this->host;
        $this->smtp = @fsockopen($host, $this->port);
        //set block mode
        //    stream_set_blocking($this->smtp, 1);
        if (!$this->smtp) {
            throw new Exception("Could not open SMTP Port.");
        }
        $code = $this->getCode();
        if ($code !== '220') {
            throw new Exception('220', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP STARTTLS
     * SUCCESS 220
     * @return $this
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    protected function starttls() {
        $in = "STARTTLS" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '220') {
            throw new Exception('220', $code, array_pop($this->resultStack));
        }
        if (!\stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception("Start TLS failed to enable crypto");
        }
        return $this;
    }

    /**
     * SMTP EHLO
     * SUCCESS 250
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function ehlo() {
        $in = "EHLO " . $this->ehlo . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '250') {
            throw new Exception('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP AUTH LOGIN
     * SUCCESS 334
     * SUCCESS 334
     * SUCCESS 235
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function authLogin() {
        $in = "AUTH LOGIN" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '334') {
            throw new Exception('334', $code, array_pop($this->resultStack));
        }
        $in = base64_encode($this->username) . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '334') {
            throw new Exception('334', $code, array_pop($this->resultStack));
        }
        $in = base64_encode($this->password) . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '235') {
            throw new Exception('235', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP AUTH OAUTHBEARER
     * SUCCESS 235
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function authOAuthBearer() {
        $authStr = sprintf("n,a=%s,%shost=%s%sport=%s%sauth=Bearer %s%s%s", $this->message->getFromEmail(), chr(1), $this->host, chr(1), $this->port, chr(1), $this->oauthToken, chr(1), chr(1)
        );
        $authStr = base64_encode($authStr);
        $in = "AUTH OAUTHBEARER $authStr" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '235') {
            throw new Exception('235', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP AUTH XOAUTH2
     * SUCCESS 235
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function authXOAuth2() {
        $authStr = sprintf("user=%s%sauth=Bearer %s%s%s", $this->message->getFromEmail(), chr(1), $this->oauthToken, chr(1), chr(1)
        );
        $authStr = base64_encode($authStr);
        $in = "AUTH XOAUTH2 $authStr" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '235') {
            throw new Exception('235', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP MAIL FROM
     * SUCCESS 250
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function mailFrom() {
        $in = "MAIL FROM:<{$this->message->getFromEmail()}>" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '250') {
            throw new Exception('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP RCPT TO
     * SUCCESS 250
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function rcptTo() {
        $to = array_merge(
                $this->message->getTo(), $this->message->getCc(), $this->message->getBcc()
        );
        foreach ($to as $toEmail => $_) {
            $in = "RCPT TO:<" . $toEmail . ">" . $this->CRLF;
            $code = $this->pushStack($in);
            if ($code !== '250') {
                throw new Exception('250', $code, array_pop($this->resultStack));
            }
        }
        return $this;
    }

    /**
     * SMTP DATA
     * SUCCESS 354
     * SUCCESS 250
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function data() {
        $in = "DATA" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '354') {
            throw new Exception('354', $code, array_pop($this->resultStack));
        }
        $in = $this->message->toString();
        $code = $this->pushStack($in);
        if ($code !== '250') {
            throw new Exception('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP QUIT
     * SUCCESS 221
     * @return $this
     * @throws Exception
     * @throws Exception
     */
    protected function quit() {
        $in = "QUIT" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '221') {
            throw new Exception('221', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    protected function pushStack($string) {
        $this->commandStack[] = $string;
        fputs($this->smtp, $string, strlen($string));
        return $this->getCode();
    }

    /**
     * get smtp response code
     * once time has three digital and a space
     * @return string
     * @throws Exception
     */
    protected function getCode() {
        while ($str = fgets($this->smtp, 515)) {
            $this->resultStack[] = $str;
            if (substr($str, 3, 1) == " ") {
                $code = substr($str, 0, 3);
                return $code;
            }
        }
        throw new Exception("SMTP Server did not respond with anything I recognized");
    }

}
