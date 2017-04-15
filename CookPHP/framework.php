<?php

use Engine\Action;
use Library\{
    Config,
    Session
};

class framework {

    public function __construct() {
        
    }

    /**
     * 运行项目
     * @access public
     * @return mixed
     */
    public function run() {
        $this->init();
        $this->execute();
    }

    /**
     * 初始项目
     * @access public
     * @return mixed
     */
    public function init() {
        $this->autoload();
        date_default_timezone_set(Config::get('default.timezone'));
        $this->initSession();
        $this->initHelper();
    }

    public function initSession() {
        Config::get('session.start') && Session::init();
    }

    public function initHelper() {
        require_once __COOK__ . 'Helper' . DS . 'Coomon.php';
    }

    /**
     * 执行项目
     * @access private
     * @return mixed
     */
    private function execute() {
        Action::execute();
    }

    /**
     * 自动加载
     * @access private
     * @return mixed
     */
    private function autoload() {
        spl_autoload_register(function ($class) {
            $init = explode('\\', $class, 2);
            in_array($init[0], ['Engine', 'Library']) ? (file_exists($file = __COOK__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') ? require $file : false) : (file_exists($file = __APP__ . rtrim($init[0] . DS . str_replace('\\', DS, $init[1] ?? null), DS) . '.class.php') ? require $file : false);
        });
    }

}
