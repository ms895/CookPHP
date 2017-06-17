<?php

/**
 * CookPHP Framework
 * @name CookPHP Framework
 * @package CookPHP
 * @author CookPHP <admin@cookphp.org>
 * @version 0.0.1 Beta
 * @link <a href="http://www.cookphp.org">CookPHP</a>
 * @copyright cookphp.org
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Engine;

use Library\{
    Db,
    Cache
};

/**
 * 模型
 */
class Model extends Loader {

    protected $table;
    protected $params;
    protected $data;
    private $db;
    private static $_db = [], $cache = false, $sqlCnt = 0;

    public function __construct($table = null, $options = null) {
        $this->db = self::$_db[serialize($options)] ?? self::$_db[serialize($options)] = new Db($options);
        $this->setTable($table);
    }

    /**
     * 设置表
     * @access public
     * @param string $table
     * @return $this
     */
    public function setTable($table = null) {
        $this->table = parse_name($table ?: (($pos = strpos($name = substr(get_class($this), strlen('Model')), '\\')) !== false ? substr($name, $pos + 1) : $name));
        $this->table();
        return $this;
    }

    /**
     * 设置table
     * 查询表
     * @access public
     * @param string $string
     * @return $this
     */
    public function table(string $string = '') {
        $this->params['table'] = $string ?: $this->table;
        return $this;
    }

    /**
     * 返回表名称
     * @access public
     * @return string
     */
    public function getTableName() {
        if (empty($this->params['table'])) {
            $this->table();
        }
        empty($this->params['table']) && trigger_error('Table Name Required');
        return $this->params['table'];
    }

    /**
     * 设置aliases
     * @param string $aliases
     * @return $this
     */
    public function alias(string $aliases) {
        $this->params['aliases'] = (string) $aliases;
        return $this;
    }

    /**
     * 设置field
     * 一般返回查询字段
     * @access public
     * @param mixed $string
     * @return $this
     */
    public function field($string = null) {
        $this->params['field'] = $string ?: '*';
        return $this;
    }

    /**
     * 设置data
     * @param array $data
     * @return $this
     */
    public function data($data) {
        $this->params['data'] = (array) $data;
        return $this;
    }

    /**
     * 设置aliases
     * @param string $aliases
     * @return $this
     */
    public function aliases(string $aliases) {
        $this->params['aliases'] = (string) $aliases;
        return $this;
    }

    /**
     * 设置join
     * 关系查询
     * @access public
     * @param array $array
     * @return $this
     */
    public function join(...$join) {
        array_map(function ($var) {
            foreach ((array) $var as $string) {
                if (!empty($string)) {
                    $this->params['join'][] = false !== stripos($string, 'JOIN') ? ' ' . $string : ' INNER JOIN ' . $string;
                }
            }
        }, $join);
        return $this;
    }

    /**
     * 设置data
     * @param array $data
     * @return $this
     */
    public function set($data) {
        $this->params['data'] = (array) $data;
        return $this;
    }

    /**
     * 设置where
     * 设置查询条件
     * @access public
     * @param array|string  $array
     * @return $this
     */
    public function where(...$array) {
        array_map(function ($var) {
            if (!empty($var)) {
                $this->params['where'][] = $var;
            }
        }, $array);
        return $this;
    }

    /**
     * 设置group
     * 用于结合合计函数，根据一个或多个列对结果集进行分组
     * @access public
     * @param string|array  $string
     * @return $this
     */
    public function group(string $string) {
        $this->params['group'] = $string;
        return $this;
    }

    /**
     * 设置order
     * 结果集进行排序 ASC|DESC
     * @access public
     * @param string $string
     * @return $this
     */
    public function order(string $string) {
        $this->params['order'] = $string;
        return $this;
    }

    /**
     * 设置limit
     * 返回指定的记录数
     * @access public
     * @param int $int
     * @return $this
     */
    public function limit(int $int) {
        $this->params['limit'] = $int;
        return $this;
    }

    /**
     * 设置offset
     * 查询结果中以第0条记录为基准（包括第0条）
     * @access public
     * @param int $int
     * @return $this
     */
    public function offset(int $int) {
        $this->params['offset'] = $int;
        return $this;
    }

    /**
     * 设置page
     * 查询页码
     * @access public
     * @param int $int
     * @return $this
     */
    public function page(int $int) {
        $this->params['page'] = $int;
        return $this;
    }

    public function columns($columns) {
        $this->params['columns'] = $columns;
        return $this;
    }

    public function indexes($indexes) {
        $this->params['indexes'] = $indexes;
        return $this;
    }

    /**
     * 清空表
     * @access public
     * @param string $table 要靖空的表
     * @return bool
     */
    public function truncate($table) {
        return $this->db->truncate($table);
    }

    /**
     * 处理字段名称
     * @param string $name
     * @return string
     */
    public function name(string $name) {
        return $this->db->name($name);
    }

    /**
     * 处理字段值
     * @param mixed $value
     * @return string
     */
    public function value(string $value) {
        return $this->db->value($value);
    }

    /**
     * 开始事务
     * @access public
     * @return bool
     */
    public function begin() {
        return $this->db->begin();
    }

    /**
     * 提交事务
     * @access public
     * @return bool
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * 回滚事务
     * @access public
     * @return bool
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     * 执行查询
     * @access public
     * @param stirng $sql
     * @return
     */
    public function query(string $sql) {
        $this->params = [];
        self::$sqlCnt++;
        return preg_match("/^(insert|delete|update|replace|drop|create)\s+/i", $sql) ? $this->db->exec($sql) : $this->db->query($sql);
    }

    /**
     * 查询一个结果集
     * @param stirng $sql
     * @return array
     */
    public function find($sql = null) {
        return $this->fetch($sql);
    }

    /**
     *  查询多个结果集
     * @param stirng $sql
     * @return array
     */
    public function findAll($sql = null) {
        return $this->select($sql);
    }

    /**
     *  查询一个结果集
     * @param stirng $sql
     * @return array
     */
    public function fetch($sql = null) {
        $sql = $sql ?: $this->db->buildStatement($this->params, $this->table);
        $this->params = [];
        return self::$cache ? Cache::remember($sql, function() use ($sql) {
                    return $this->limit(1)->query($sql)->fetch();
                }) : $this->limit(1)->query($sql)->fetch();
    }

    /**
     * 查询数据
     * @param stirng $sql
     */
    public function select($sql = null) {
        $sql = $sql ?: $this->db->buildStatement($this->params, $this->table);
        $this->params = [];
        return self::$cache ? Cache::remember($sql, function() use ($sql) {
                    return $this->query($sql)->fetchAll();
                }, is_number_id(self::$cache) ? self::$cache : null) : $this->query($sql)->fetchAll();
    }

    /**
     *  查询多个结果集
     * @param stirng $sql
     * @return array
     */
    public function fetchAll($sql = null) {
        return $this->select($sql);
    }

    /**
     * 开启缓存
     * @return $this
     */
    public function cacheOn($expire = null) {
        self::$cache = is_number_id($expire) ? $expire : (config('db.cache') ?: true);
        return $this;
    }

    /**
     * 关闭缓存
     * @return $this
     */
    public function cacheOff() {
        self::$cache = false;
        return $this;
    }

    /**
     * 确定目标数据是否存在
     * @param stirng $sql
     * @return bool
     */
    public function has($sql = null) {
        return $this->query('SELECT EXISTS(' . ($sql ?: $this->db->buildStatement($this->params, $this->table)) . ')')->fetchColumn() === '1';
    }

    /**
     * 统计总数
     * @param stirng $field 字段
     * @return int
     */
    public function count($field = '*') {
        return $this->limit(1)->field('COUNT(' . $this->name($field) . ')')->query($this->db->buildStatement($this->params, $this->table))->fetchColumn();
    }

    /**
     * 最大值
     * @param stirng $field 字段
     * @return int
     */
    public function max($field) {
        return $this->limit(1)->field('MAX(' . $this->name($field) . ')')->query($this->db->buildStatement($this->params, $this->table))->fetchColumn();
    }

    /**
     * 最小值
     * @param stirng $field 字段
     * @return int
     */
    public function min($field) {
        return $this->limit(1)->field('MIN(' . $this->name($field) . ')')->query($this->db->buildStatement($this->params, $this->table))->fetchColumn();
    }

    /**
     * 平均值
     * @param stirng $field 字段
     * @return int
     */
    public function avg($field) {
        return $this->limit(1)->field('AVG(' . $this->name($field) . ')')->query($this->db->buildStatement($this->params, $this->table))->fetchColumn();
    }

    /**
     * 总数
     * @param stirng $field 字段
     * @return int
     */
    public function sum($field) {
        return $this->limit(1)->field('SUM(' . $this->name($field) . ')')->query($this->db->buildStatement($this->params, $this->table))->fetchColumn();
    }

    /**
     * 执行事务
     * @param callable $actions
     * @return boolean
     */
    public function action($actions) {
        return $this->db->action($actions);
    }

    /**
     * 新增数据
     * @access public
     * @param bool  $replace 是否replace新增
     * @return bool
     */
    public function save($replace = false) {
        return $this->insert($replace);
    }

    /**
     * 新增数据
     * @access public
     * @param bool  $replace 是否replace新增
     * @return bool
     */
    public function create($replace = false) {
        return $this->insert($replace);
    }

    /**
     * 新增数据
     * 如果成功返回影响的行数
     * @access public
     * @param bool  $replace 是否replace新增
     * @return int|bool
     */
    public function insert(bool $replace = false) {
        $this->params['replace'] = (boolean) $replace;
        $data = $this->params['data'] ?? null;
        if (empty($data)) {
            return false;
        }
        $field = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $field = array_keys($value);
            } else {
                $field[] = $key;
            }
        }
        $this->field($field);
        return $this->query($this->db->buildStatement($this->params, $this->table, 'insert')) ?: false;
    }

    /**
     * 更新数数据
     * 如果成功返回影响的行数
     * @return bool|int
     */
    public function update() {
        $data = $this->params['data'] ?? null;
        if (empty($data)) {
            return false;
        }
        unset($this->params['data']);
        $this->params['set'] = $data;
        return $this->query($this->db->buildStatement($this->params, $this->table, 'update')) ?: false;
    }

    /**
     * 删除数据
     * 如果成功返回影响的行数
     * @return int|bool
     */
    public function delete() {
        return $this->query($this->db->buildStatement($this->params, $this->table, 'delete')) ?: false;
    }

    /**
     * 得到分表名
     * @param string|int $value
     * @param int $num
     * @return string
     */
    public function getPartition($value, $num = 10) {
        $this->table( $this->table. '_' . (is_number_id($value) ? ($value % $num) : (is_string($value) ? (ord(substr(md5($value), 0, 1)) % $num) : (ord($value{0}) % $num))));
        return $this;
    }

    public function schema() {
        
    }

    /**
     * 最后插入行的ID或序列值
     * @return int
     */
    public function id() {
        return $this->db->id();
    }

    /**
     * 开启调试
     * @return $this
     */
    public function debug() {
        $this->debug_mode = true;

        return $this;
    }

    /**
     * 返回错误
     * @return array
     */
    public function error() {
        return $this->db->error();
    }

    /**
     * 最后一条SQl
     * @return string
     */
    public function last() {
        return $this->db->last();
    }

    /**
     * SQL日志
     * @return array
     */
    public function log() {
        return $this->db->log();
    }

    /**
     * 返回服务器信息
     * @return array
     */
    public function info() {
        return $this->db->info();
    }

}
