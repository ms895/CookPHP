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

namespace Engine;

use Library\{
    Config,
    File,
    Log
};

/**
 * 视图
 * @author CookPHP <admin@cookphp.org>
 */
class View {

    //定义视图模板解析左标示
    protected $left = '{';
    //定义视图模板解析右标示
    protected $right = '}';
    //定义视图模板文件后缀
    protected $tplsuffix = '.tpl';
    //定义视图编译文件后缀
    protected $compilesuffix = '.php';
    //定义视图缓存文件后缀
    protected $cachesuffix = '.html';
    //定义视图模板是否运行插入PHP代码
    protected $php = false;
    //定义视图模板是否持续编译
    protected $conver = false;
    //定义视图模板是否压缩html
    protected $compresshtml = false;
    //定义是否开启视图模板布局
    protected $layout = false;
    //定义是否开启视图模板布局入口文件名
    protected $layoutname = 'Public:layout';
    //定义视图模板输出替换变量
    protected $layoutitem = '{__REPLACE__}';
    //替换标签
    protected $parsestring = [];
    //定义视图模板缓存时间(单位秒) 0永远缓存 缓存一个小时：3600
    protected $cachetime = 0;
    //是否显示页面Trace信息
    protected $showtrace = false;
    //视图模板样式
    protected $theme = '';
    //缓存目录
    protected $cachedir = '';
    //编译目录
    protected $compiledir = '';
    //压缩等级 0-9
    protected $compression = 9;
    //模板目录
    protected $templateDir = [];
    // 模板变量
    private $_vars = [];
    //编译时间
    private $compileUsageTime;

    public function __construct() {
        $this->init();
    }

    private function init() {
        APP_ROUTE ? $this->setTemplateDir(__VIEWS__ . APP_ROUTE . DS) : $this->setTemplateDir(__VIEWS__);
        
        foreach (Config::get('view') as $key => $value) {
            if (!is_null($value)) {
                $this->$key = $value;
            }
        }
        //$this->setCacheDir(Config::get('view.cachedir'))->setCompileDir(Config::get('view.compiledir'));
    }

    /**
     * 渲染模板
     * @access public
     * @param string $template 模板
     * @param mixed $data 赋值
     * @param bool $return 是否直接返回，默认false输出
     */
    public function render($template = null, $data = null, $return = false) {
        !empty($data) && $this->assign($data);
        $content = $this->fetch($template);
        if ($return) {
            return $content;
        } else {
            echo $content;
        }
    }

    /**
     * 赋值
     * @param string|array $var
     * @param mixed $value
     */
    public function assign($var, $value = null) {
        is_array($var) ? ($this->_vars = array_merge($this->_vars, $var)) : ($this->_vars[$var] = $value);
    }

    /**
     * 获取模板变量
     *
     * @param string $name
     * @return null|mixed
     */
    public function getVar($name = '') {
        return $name === '' ? $this->_vars : ($this->_vars[$name] ?? null);
    }

    /**
     * 取得输出内容
     * @access protected
     * @param string $template 模板
     * @param string $id 识别ID
     * @return string
     */
    public function fetch($template = null) {
        $this->replaceTemplate($template);
        $this->getTemplateFile($template);
        $compileFile = $this->getCompileFile($template);
        $time = microtime(true);
        $this->compile($template, $compileFile);
        $this->compileUsageTime = Log::getUsageTime($time, microtime(true));
        ob_start();
        ob_implicit_flush(0);
        if (File::has($compileFile)) {
            extract($this->getVar());
            require $compileFile;
        }
        $content = ob_get_clean();
        header('X-Powered-By:CookPHP');
        return $this->parseString($content);
    }

    /**
     * 显示输出内容
     * @access protected
     * @param string $template 模板
     * @param string $template
     */
    protected function display($template = '') {
        echo $this->fetch($template);
    }

    /**
     * 返回模板后缀
     * @access protected
     * @return string
     */
    protected function getSuffix(): string {
        return $this->tplsuffix;
    }

    /**
     * 检测是否支持
     * @access protected
     * @param string  $template  模板
     * @param string $type
     * @return bool
     */
    protected function supports(string $template, $type = null): bool {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), [$type ?: 'tpl']);
    }

    /**
     * 设置缓存目录
     * @access protected
     * @param string $dir
     * @return $this
     */
    protected function setCacheDir(string $dir) {
        $this->cachedir = $dir;
        return $this;
    }

    /**
     * 设置编译目录
     * @access protected
     * @param string $dir
     * @return $this
     */
    protected function setCompileDir(string $dir) {
        $this->compiledir = $dir;
        return $this;
    }

    /**
     * 设置模板目录
     * @access protected
     * @param string|array $dir
     * @return $this
     */
    protected function setTemplateDir(string ...$dir) {
        array_map(function ($var) {
            if (is_array($var)) {
                foreach ($var as $value) {
                    $this->templateDir[] = (string) $value;
                }
            } else {
                $this->templateDir[] = (string) $var;
            }
        }, $dir);
        return $this;
    }

    /**
     * 获取模板路径
     * @access private
     * @param string $template
     * @return string
     */
    private function getTemplateFile(&$template): string {
        $tpl = '';
        $template = str_ireplace(':', DS, trim($template, ':')) . $this->tplsuffix;
        foreach ($this->templateDir as $path) {
            if (is_readable(($path = rtrim($path, '/\\') . ($this->theme ? DS . rtrim($this->theme, '/\\') . DS : DS) . $template))) {
                $tpl = $path;
                break;
            }
        }
        empty($tpl) && exit('Template does not exist:' . $template);
        $template = $tpl;
        return $template;
    }

    /**
     * 解析模板名称
     * @access protected
     * @param string $template
     * @return string
     */
    private function replaceTemplate(&$template) {
        if (empty($template)) {
            $template = APP_CONTROLLER . ':' . APP_METHOD;
        } elseif (is_readable($template)) {
            return $template;
        } elseif (stristr($template, ':') === false) {
            $template = APP_CONTROLLER . ':' . $template;
        }
        $template = str_ireplace(':', DS, trim($template, ':'));
        return $template;
    }

    /**
     * 返回编辑文件
     * @access private
     * @param string $template
     * @return string
     */
    private function getCompileFile(string $template): string {
        return rtrim($this->compiledir, '/\\') . DS . $this->filename($template) . $this->compilesuffix;
    }

    /**
     * 返回缓存文件
     * @access private
     * @param string $template
     * @return string
     */
    private function getCacheFile(string $template): string {
        return rtrim($this->cachedir, '/\\') . DS . $this->filename($template) . $this->cachesuffix;
    }

    /**
     * 取得存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    private function filename(string $name): string {
        $name = md5($name);
        $dir = '';
        for ($i = 0; $i < 6; $i++) {
            $dir .= $name{$i} . DS;
        }
        return $dir . $name;
    }

    private $_preg, $_replace, $_left, $_right;

    /**
     * 去掉UTF-8 Bom头
     * @param  string    $string
     * @access protected
     * @return string
     */
    private function removeUTF8Bom($string) {
        return substr($string, 0, 3) == pack('CCC', 239, 187, 191) ? substr($string, 3) : $string;
    }

    /**
     * 编译
     * @access private
     * @param string $template
     * @param string $compileFile
     */
    private function compile(string $template, string $compileFile) {
        $content = trim($this->removeUTF8Bom(File::get($template)));
        $this->_left = '(?<!!)' . $this->stripPreg($this->left);
        $this->_right = '((?<![!]))' . $this->stripPreg($this->right);
        if ($this->layout) {
            $content = trim($this->parseLayout($content));
        }
        $content = $this->compileInclude($content);
        if (!File::has($compileFile) || !$this->conver || ($md5 = md5($content)) !== File::get($compileFile, 8, 32)) {
            if (!empty($content)) {
                $this->compileCode($content);
                ($this->compresshtml || Config::get('view.compresshtml')) && $this->compressHtml($content);
            }
            File::set($compileFile, "<?php\n//" . (!empty($md5) ? $md5 : '') . "\n?>" . $content);
        }
    }

    /**
     * 压缩HTML
     * @access private
     * @param string $content
     * @return string
     */
    private function compressHtml(&$content): string {
        $content = preg_replace(['/\?><\?php/', '~>\s+<~', '~>(\s+\n|\r)~', "/> *([^ ]*) *</", "/[\s]+/", "/<!--[^!]*-->/", "/ \"/", "'/\*[^*]*\*/'"], ['', '><', '>', ">\\1<", ' ', '', "\"", ''], $content);
        return $content;
    }

    /**
     * 解析布局
     * @access private
     * @param string $content
     */
    private function parseLayout(string $content): string {
        $layout = File::get($this->getTemplateFile($this->layoutname));
        $layout = $this->compileInclude($layout);
        $pattern = '/' . $this->_left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->_right . '(.*?)' . $this->_left . '\/block' . $this->_right . '/is';
        if (preg_match($pattern, $layout)) {
            preg_replace_callback($pattern, [$this, 'parseBlock'], $content);
            $layout = $this->replaceBlock($layout);
            return str_replace($this->layoutitem, preg_replace($pattern, '', $content), $layout);
        } else {
            return str_replace($this->layoutitem, $content, $layout);
        }
    }

    private $_block;

    /**
     * 记录当前页面中的block标签
     * @access private
     * @param string $name block名称
     * @return string
     */
    private function parseBlock($name): string {
        $this->_block[$name[1]] = $name[3];
        return '';
    }

    /**
     * 替换继承模板中的block标签
     * @access private
     * @param string $content 模板内容
     * @return string
     */
    private function replaceBlock($content): string {
        static $parse = 0;
        if (is_string($content)) {
            do {
                $content = empty($content) ? '' : preg_replace_callback('/(' . $this->_left . 'block\sname=[\'"](.+?)[\'"]\s*?' . $this->_right . ')(.*?)' . $this->_left . '\/block' . $this->_right . '/is', [$this, 'replaceBlock'], $content);
            } while ($parse && $parse--);
            return $content;
        } elseif (is_array($content)) {
            return $this->_block[$content[2]] ?? $content[4];
        }
    }

    /**
     * 编译导入文件
     * @access private
     * @param string $content
     */
    private function compileInclude($content): string {
        $content = empty($content) ? '' : preg_replace_callback('/' . $this->_left . 'include\sfile=[\'"](.+?)[\'"]\s*?' . $this->_right . '/is', [$this, 'parseInclude'], $content);
        return $content;
    }

    /**
     * 解析导入文件
     * @access private
     * @param array $content
     * @return string
     */
    private function parseInclude($content): string {
        $template = stripslashes($content[1]);
        $this->replaceTemplate($template);
        $this->getTemplateFile($template);
        return $this->compileInclude(File::get($template));
    }

    /**
     * 转换标示符
     * @access private
     * @param string $tag
     * @return string
     */
    private function stripPreg($tag): string {
        return str_replace(['{', '}', '(', ')', '|', '[', ']', '-', '+', '*', '.', '^', '?'], ['\{', '\}', '\(', '\)', '\|', '\[', '\]', '\-', '\+', '\*', '\.', '\^', '\?'], $tag);
    }

    /**
     * 编译代码
     * @access private
     * @param string $content
     */
    private function compileCode(&$content) {
        $content = preg_replace_callback('/' . $this->_left . 'literal' . $this->_right . '(.*?)' . $this->_left . '\/literal' . $this->_right . '/is', [$this, 'parseLiteral'], $content);
        $this->compileVar($content);
        !$this->php && $this->replacePHP();
        $this->_preg();
        $this->_replace();
        $content = preg_replace($this->_preg, $this->_replace, $content);
        $content = str_replace(['!' . $this->_left, '!' . $this->_right], [$this->_left, $this->_right], $content);
        $content = preg_replace_callback('/<!--###literal(\d+)###-->/is', [$this, 'restoreLiteral'], $content);
        $content = preg_replace_callback("/##XML(.*?)XML##/s", [$this, 'xmlSubstitution'], $content);
    }

    private $_literal = [];

    /**
     * 替换页面中的literal标签
     *
     * @access private
     * @param string $content 模板内容
     * @return string|false
     */
    private function parseLiteral($content) {
        if (is_array($content)) {
            $content = $content[2];
        }
        if (trim($content) == '') {
            return '';
        }
        $i = count($this->_literal);
        $parseStr = "<!--###literal{$i}###-->";
        $this->_literal[$i] = $content;
        return $parseStr;
    }

    /**
     * 还原被替换的literal标签
     *
     * @access private
     * @param string $tag literal标签序号
     * @return string|false
     */
    private function restoreLiteral($tag) {
        if (is_array($tag)) {
            $tag = $tag[1];
        }
        $parseStr = $this->_literal[$tag];
        unset($this->_literal[$tag]);
        return $parseStr;
    }

    /**
     * 编译变量
     * @access private
     * @param string $content
     */
    private function compileVar(&$content) {
        $content = preg_replace_callback('/(' . $this->_left . ')([^\d\s].+?)(' . $this->_right . ')/is', [$this, 'parseTag'], $content);
        return $content;
    }

    /**
     * 解析标签
     * @access private
     * @param array $content
     * @return string
     */
    private function parseTag($content) {
        $content = preg_replace_callback('/\$\w+((\.\w+)*)?/', [$this, 'parseVar'], stripslashes($content[0]));
        return $content;
    }

    /**
     * 解析变量
     * @access private
     * @param array $var
     * @return string
     */
    private function parseVar($var) {
        if (empty($var[0])) {
            return '';
        }
        $vars = explode('.', $var[0]);
        $name = array_shift($vars);
        foreach ($vars as $val) {
            $name .= '["' . trim($val) . '"]';
        }
        return $name;
    }

    /**
     * 替换PHP标签
     * @access private
     */
    private function replacePHP() {
        $this->_preg[] = '/<\?(=|php|)(.+?)\?>/is';
        $this->_replace[] = '&lt;?\\1\\2?&gt;';
    }

    /**
     * 处理模板语法
     * @access private
     */
    private function _preg() {
        $this->_preg[] = '/' . $this->_left . '(else if|elseif) (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'for (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'while (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . '(loop|foreach) (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'if (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . 'else' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . "(eval|_)( |[\r\n])(.*?)" . $this->_right . '/is';
        $this->_preg[] = '/' . $this->_left . ':(.*?)' . $this->_right . '/is';
        $this->_preg[] = '/' . $this->_left . '_e (.*?)' . $this->_right . '/is';
        $this->_preg[] = '/' . $this->_left . '_p (.*?)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . '\/(if|for|loop|foreach|eval|while)' . $this->_right . '/i';
        $this->_preg[] = '/' . $this->_left . '(([_a-zA-Z][\w]*\(.*?\))|\$((\w+)(\[(\'|")?\$*\w*(\'|")?\])*(->)?(\w*)(\((\'|")?(.*?)(\'|")?\)|)))' . $this->_right . '/i';
        $this->_preg[] = "/(	| ){0,}(\r\n){1,}\";/";
        $this->_preg[] = '/' . $this->_left . '(\#|\*)(.*?)(\#|\*)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . '\@(.*?)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . '\#(.*?)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . '\%(.*?)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . 'Lib (.*?)' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . 'LANGUAGE' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . 'CompileTime' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . 'UsageTime' . $this->_right . '/';
        $this->_preg[] = '/' . $this->_left . 'UsageMemory' . $this->_right . '/';
    }

    /**
     * 模板语法替换
     * @access private
     */
    private function _replace() {
        $this->_replace[] = '<?php }else if (\\2){ ?>';
        $this->_replace[] = '<?php for (\\1) { ?>';
        $this->_replace[] = '<?php while (\\1) { ?>';
        $this->_replace[] = '<?php foreach (\\2) { ?>';
        $this->_replace[] = '<?php if (\\1){ ?>';
        $this->_replace[] = '<?php }else{ ?>';
        $this->_replace[] = '<?php \\3; ?>';
        $this->_replace[] = '<?php echo \\1; ?>';
        $this->_replace[] = '<?php echo \\1; ?>';
        $this->_replace[] = '<?php print_r(\\1); ?>';
        $this->_replace[] = '<?php } ?>';
        $this->_replace[] = '<?php echo \\1;?>';
        $this->_replace[] = '';
        $this->_replace[] = '';
        $this->_replace[] = '<?php echo \Library\Config::get("\\1");?>';
        $this->_replace[] = '<?php echo \Library\Url::parse("\\1");?>';
        $this->_replace[] = '<?php echo \Library\Language::get("\\1");?>';
        $this->_replace[] = '<?php echo \Library\ \\1;?>';
        $this->_replace[] = '<?php echo LANGUAGE;?>';
        $this->_replace[] = '<?php echo $this->compileUsageTime;?>';
        $this->_replace[] = '<?php echo \Library\Log::getUsageTime();?>';
        $this->_replace[] = '<?php echo \Library\Log::getUsageMemory();?>';
    }

    /**
     * 替换标签
     * @access private
     * @param string $content
     * @return string|mixed
     */
    private function parseString($content): string {
        $replace = $this->parsestring;
        return !empty($replace) ? str_replace(array_keys($replace), array_values($replace), $content) : $content;
    }

    /**
     * 处理XML
     * @access private
     * @param string $capture
     */
    private function xmlSubstitution($capture): string {
        return "<?php echo '<?xml " . stripslashes($capture[1]) . " ?>'; ?>";
    }

}
