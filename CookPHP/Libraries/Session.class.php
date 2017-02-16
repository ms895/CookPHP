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

namespace Libraries;

use Core\{
    Config,
    Loader
};

/**
 * Session类
 * @author CookPHP <admin@cookphp.org>
 */
class Session {

    public static function init() {
//        ini_set('session.save_handler', strtolower(Config::get('session.driver')));
//        ini_set('session.save_path', Config::get('session.path'));
        session_set_cookie_params((Config::get('cookie.lifetime') ? 0 : time() + Config::get('cookie.lifetime')), Config::get('cookie.path'), Config::get('cookie.domain'), Config::get('cookie.secure'), Config::get('cookie.httponly'));
        session_name(Config::get('session.name'));
        session_set_save_handler(Loader::initialize(false !== strpos(Config::get('session.driver'), '\\') ? Config::get('session.driver') : __NAMESPACE__ . '\\Session\\' . ucwords(Config::get('session.driver'))), true);
        session_start();
    }

}
