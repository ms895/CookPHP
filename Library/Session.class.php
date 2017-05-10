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
 * Session类
 * @author CookPHP <admin@cookphp.org>
 */
class Session {

    public static function init() {
        session_module_name(strtolower(Config::get('session.module')));
        session_save_path(Config::get('session.path'));
        session_set_cookie_params((Config::get('cookie.lifetime') ? 0 : time() + Config::get('cookie.lifetime')), Config::get('cookie.path'), Config::get('cookie.domain'), Config::get('cookie.secure'), Config::get('cookie.httponly'));
        session_name(Config::get('session.name'));
        session_start();
    }

}
