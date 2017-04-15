<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library;

/**
 * Description of Exceptions
 *
 * @author 费尔
 */
class Exception {

    public static $obLevel;
    public static $levels = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parsing Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice'
    ];

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        echo $message;
    }

    /**
     * 生成错误日志消息
     * @param	int	$severity	日志级别
     * @param	string	$message	错误信息
     * @param	string	$filepath	文件路径
     * @param	int	$line		行
     * @return	void
     */
    public static function logException($severity, $message, $filepath, $line) {
        $severity = self::$levels[$severity] ?? $severity;
        self::logMessage('error', 'Severity: ' . $severity . ' --> ' . $message . ' ' . $filepath . ' ' . $line);
    }

    public static function logMessage($level, $message) {
        //echo $message;
    }

    /**
     * 404错误处理
     * @param	string	$page		页面
     * @param 	bool	$logError	是否记录错误
     * @return	void
     */
    public static function show404($page = '', $logError = true) {
        
        print_r(error_get_last());
        
        if (IS_CLI) {
            $heading = 'Not Found';
            $message = 'The controller/method pair you requested was not found.';
        } else {
            $heading = '404 Page Not Found';
            $message = 'The page you requested was not found.';
        }
        if ($logError) {
            self::logMessage('error', $heading . ': ' . $page);
        }

        exit(self::showError($heading, $message, 'error_404', 404));
    }

    // --------------------------------------------------------------------

    /**
     * 一般错误页面
     * @param	string		$heading	Page heading
     * @param	string|string[]	$message	Error message
     * @param	string		$template	Template name
     * @param 	int		$status_code	(default: 500)
     *
     * @return	string	Error page output
     */
    public static function showError($heading, $message, $template = 'error_general', $status_code = 500) {
        if (IS_CLI) {
            $message = "\t" . (is_array($message) ? implode("\n\t", $message) : $message);
            $template = 'Cli' . DS . $template;
        } else {
            //Error::setStatusHeader($status_code);
            $message = '<p>' . (is_array($message) ? implode('</p><p>', $message) : $message) . '</p>';
            $template = 'Html' . DS . $template;
        }
        if (ob_get_level() > self::$obLevel + 1) {
            ob_end_flush();
        }
        ob_start();
        require (__ERROR__ . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    public static function showException($exception) {
        $message = $exception->getMessage();
        if (empty($message)) {
            $message = '(null)';
        }
        if (IS_CLI) {
            $templatesPath = 'Cli' . DS;
        } else {
            $templatesPath = 'Html' . DS;
        }

        if (ob_get_level() > self::$obLevel + 1) {
            ob_end_flush();
        }

        ob_start();
        require (__ERROR__ . $templatesPath . 'error_exception.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }

    /**
     * PHP错误处理程序
     * @param	int	$severity	日志级别
     * @param	string	$message	错误信息
     * @param	string	$filepath	文件路径
     * @param	int	$line		行
     * @return	void
     */
    public static function showPHPError($severity, $message, $filepath, $line) {
        $severity = self::$levels[$severity] ?? $severity;
        if (IS_CLI) {
            $filepath = str_replace('\\', '/', $filepath);
            if (false !== strpos($filepath, '/')) {
                $x = explode('/', $filepath);
                $filepath = $x[count($x) - 2] . '/' . end($x);
            }
            $template = 'Cli' . DS . 'error_php';
        } else {
            $template = 'Html' . DS . 'error_php';
        }
        if (ob_get_level() > self::$obLevel + 1) {
            ob_end_flush();
        }
        ob_start();
        require (__ERROR__ . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }

}
