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

namespace Libraries\Session;

use Core\{
    Config,
    Input,
    Log,
    Error
};

class Redis implements \SessionHandlerInterface {

    protected $handler = null, $_config = [];

    /**
     * 打开Session
     * @access public
     * @param string $savePath
     * @param mixed  $sessName
     * @return bool
     * @throws Exception
     */
    public function open($savePath, $sessName) {
        if (!extension_loaded('redis')) {
            Error::showErrorError('not support:redis');
        }
        if (!Config::get('session.path')) {
            Error::showError('Session: No Redis save path configured.');
        } elseif (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', Config::get('session.path'), $matches)) {
            isset($matches[3]) or $matches[3] = '';
            $this->_config = [
                'host' => $matches[1],
                'port' => empty($matches[2]) ? null : $matches[2],
                'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : null,
                'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : null,
                'timeout' => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float) $match[1] : null,
                'prefix' => preg_match('#prefix=([^\s&]+)#', $matches[3], $match) ? $match[1] : '',
            ];
        } else {
            Error::showError('Session: Invalid Redis save path format: ');
        }
        if (Config::get('session.ip')) {
            $this->_config['prefix'] .= Input::clientIP();
        }
        $this->handler = new \Redis;
        !isset($this->_config['timeout']) || $this->_config['timeout'] === false ? $this->handler->pconnect($this->_config['host'], $this->_config['port']) : $this->handler->connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
        if (!empty($this->_config['password'])) {
            $this->handler->auth($this->_config['password']);
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close() {
        $this->gc(ini_get('session.gc_maxlifetime'));
        $this->handler->close();
        $this->handler = null;
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     * @return bool|string
     */
    public function read($sessID) {
        return Log::setLog('SessionRedis', 'get: ' . $sessID, function () use ($sessID) {
                    return $this->handler->get($this->_config['prefix'] . $sessID);
                });
    }

    /**
     * 写入Session
     * @access public
     * @param string $sessID
     * @param String $sessData
     * @return bool
     */
    public function write($sessID, $sessData) {
        return Log::setLog('SessionRedis', 'write: ' . $sessID, function () use ($sessID, $sessData) {
                    if (Config::get('session.expiration') > 0) {
                        return $this->handler->setex($this->_config['prefix'] . $sessID, Config::get('session.expiration'), $sessData);
                    } else {
                        return $this->handler->set($this->_config['prefix'] . $sessID, $sessData);
                    }
                });
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     * @return bool|void
     */
    public function destroy($sessID) {
        Log::setLog('SessionRedis', 'remove: ' . $sessID, function () use ($sessID) {
            $this->handler->delete($this->_config['prefix'] . $sessID);
        });
        return true;
    }

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     * @return bool
     */
    public function gc($sessMaxLifeTime) {
        return true;
    }

}
