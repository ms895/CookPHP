<?php

/*
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

use PDOException;
use PDO;
use Exception;

/**
 * 数据库驱动
 * @author CookPHP <admin@cookphp.org>
 * MySQL
 * MariaDB
 * MSSQL (Windows
 *  MSSQL (Linux/UNIX)
 *  Oracle
 * SQLite
 * PostgreSQL
 * Sybase
 */
class Db {

    public $type;
    public $prefix;
    public $charset;
    public $option = [];
    public $logs = [];
    public $debug_mode = false;
    public $pdo, $query;
    public $selectSql = 'SELECT %FIELD% FROM %TABLE%%ALIASES%%JOIN%%WHERE%%GROUP%%ORDER%%LIMIT%';
    public $insertSql = '%INSERT% INTO %TABLE% (%FIELD%) VALUES (%DATA%)';
    public $updateSql = 'UPDATE %TABLE%%ALIASES% SET %SET% %WHERE%';
    public $deleteSql = 'DELETE FROM %TABLE% %ALIASES%%WHERE%%LIMIT%';
    public $createTableSql = "CREATE TABLE %TABLE% (\n%COLUMNS%%INDEXES%)";
    public $alias = ' AS ';

    /**
     * 字段转义符号开始
     * @var string
     */
    public $startQuote = '"';

    /**
     * 字段转义符号结束
     * @var string
     */
    public $endQuote = '"';

    public function __construct($options = null) {
        try {
            if (empty($options) || !is_array($options)) {
                $options = Config::get('db');
            }
            $this->type = strtolower($options['type']);
            $this->prefix = $options['prefix'] ?? '';
            $this->charset = $options['charset'] ?? '';
            $this->option = $options['option'] ?? '';
            if (isset($options['command']) && is_array($options['command'])) {
                $commands = $options['command'];
            } else {
                $commands = [];
            }

            if (isset($options['port']) && is_int($options['port'])) {
                $port = $options['port'];
            }
            $is_port = isset($port);
            $attr = [];
            switch ($this->type) {
                case 'mariadb':
                case 'mysql':
                    $attr = [
                        'driver' => 'mysql',
                        'dbname' => $options['name']
                    ];
                    if (!empty($options['socket'])) {
                        $attr['unix_socket'] = $options['socket'];
                    } else {
                        $attr['host'] = $options['server'];
                        if ($is_port) {
                            $attr['port'] = $port;
                        }
                    }
                    $this->updateSql = 'UPDATE %TABLE%%ALIASES% SET %SET% %WHERE%%LIMIT%';
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    $this->startQuote = '`';
                    $this->endQuote = '`';
                    break;
                case 'pgsql':
                    $attr = [
                        'driver' => 'pgsql',
                        'host' => $options['server'],
                        'dbname' => $options['name']
                    ];
                    if ($is_port) {
                        $attr['port'] = $port;
                    }
                    $this->createTableSql = "CREATE TABLE %TABLE% (\n\t%COLUMNS%\n);\n%INDEXES%";
                    break;
                case 'sybase':
                    $attr = [
                        'driver' => 'dblib',
                        'host' => $options['server'],
                        'dbname' => $options['name']
                    ];
                    if ($is_port) {
                        $attr['port'] = $port;
                    }
                    break;
                case 'oracle':
                    $attr = [
                        'driver' => 'oci',
                        'dbname' => $options['server'] ? '//' . $options['server'] . ($is_port ? ':' . $port : ':1521') . '/' . $options['name'] : $options['name']
                    ];
                    if (isset($options['charset'])) {
                        $attr['charset'] = $options['charset'];
                    }
                    break;
                case 'mssql':
                    if (IS_WIN) {
                        $attr = [
                            'driver' => 'sqlsrv',
                            'server' => $options['server'],
                            'database' => $options['name']
                        ];
                    } else {
                        $attr = [
                            'driver' => 'dblib',
                            'host' => $options['server'],
                            'dbname' => $options['name']
                        ];
                    }
                    if ($is_port) {
                        $attr['port'] = $port;
                    }
                    $commands[] = 'SET QUOTED_IDENTIFIER ON';
                    $commands[] = 'SET ANSI_NULLS ON';
                    $this->createTableSql = "CREATE TABLE %TABLE% (\n\t%COLUMNS%\n);\n%INDEXES%";
                    $this->selectSql = 'SELECT %LIMIT%%FIELD% FROM %TABLE%%ALIASES%%JOIN%%WHERE%%GROUP%%ORDER%';
                    $this->selectSql2 = 'SELECT * FROM (SELECT %LIMIT% * FROM (SELECT TOP %OFFSET%%FIELD% FROM %TABLE%%ALIASES%%JOIN%%WHERE%%GROUP%%ORDER%) AS Set1 %RORDER%) AS Set2 %ORDER2%';
                    $this->startQuote = '[';
                    $this->endQuote = ']';
                    break;
                case 'sqlite':
                    $dsn = 'sqlite:' . $options['file'];
                    $options['username'] = null;
                    $options['password'] = null;
                    break;
                //$this->pdo = new \PDO('sqlite:' . $options['file'], null, null, $this->option);
            }
            if (empty($dsn)) {
                $driver = $attr['driver'];
                unset($attr['driver']);
                $stack = [];
                foreach ($attr as $key => $value) {
                    if (is_int($key)) {
                        $stack[] = $value;
                    } else {
                        $stack[] = $key . '=' . $value;
                    }
                }
                $dsn = $driver . ':' . implode($stack, ';');
                if (in_array($this->type, ['mariadb', 'mysql', 'pgsql', 'sybase', 'mssql']) && $options['charset']) {
                    $commands[] = "SET NAMES '" . $options['charset'] . "'";
                }
            }
            $this->pdo = new PDO($dsn, $options['username'], $options['password'], $this->option);
            foreach ($commands as $value) {
                $this->exec($value);
            }
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * 查询SQl
     * @param string $sql
     * @return boolean
     */
    public function query(string $sql) {
        Log::setLog('sql', $sql, function () use ($sql) {
            try {
                $this->query = $this->pdo->query($sql);
                //echo $sql . PHP_EOL;
            } catch (PDOException $e) {
                throw new Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
            }
        });
        return $this;
    }

    /**
     * 执行SQl
     * @param string $sql
     * @return boolean
     */
    public function exec(string $sql) {
        try {
            return Log::setLog('sql', $sql, function () use ($sql) {
                        return $this->pdo->exec($sql);
                    });
        } catch (PDOException $e) {
            throw new Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
        }
    }

    /**
     *  返回一个结果集
     * @return array
     */
    public function fetch() {
        return $this->query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     *  返回多个结果集
     * @return array
     */
    public function fetchAll() {
        return $this->query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 返回单独的一列
     * @return mixed
     */
    public function fetchColumn() {
        return $this->query->fetchColumn();
    }

    /**
     * 清空表
     * @access public
     * @param string $table 要靖空的表
     * @return bool
     */
    public function truncate($table) {
        return $this->exec('TRUNCATE TABLE ' . $this->table($table));
    }

    /**
     * 安全处理查询的字符串
     * @param string $string
     * @return string
     */
    public function name($data) {
        if (!is_array($data)) {
            $data = preg_split('/\s*,\s*/', trim($data), -1, PREG_SPLIT_NO_EMPTY);
        }
        for ($i = 0; $i < count($data); ++$i) {
            if ($data[$i] == '*') {
                continue;
            }
            if (strpos($data[$i], '(') !== false && preg_match_all('/([^(]*)\((.*)\)(.*)/', $data[$i], $field)) {
                $fe = [];
                foreach ($field as $field) {
                    $fe[] = $field[0];
                }
                $field = $fe;
                if (!empty($field[1])) {
                    if (!empty($field[2])) {
                        $data[$i] = $field[1] . '(' . $this->name($field[2]) . ')' . $field[3];
                    } else {
                        $data[$i] = $field[1] . '()' . $field[3];
                    }
                }
            }
            $data[$i] = str_replace('.', $this->endQuote . '.' . $this->startQuote, $data[$i]);
            $data[$i] = $this->startQuote . $data[$i] . $this->endQuote;
            $data[$i] = str_replace($this->startQuote . $this->startQuote, $this->startQuote, $data[$i]);
            $data[$i] = str_replace($this->startQuote . '(', '(', $data[$i]);
            $data[$i] = str_replace(')' . $this->startQuote, ')', $data[$i]);
            $alias = !empty($this->alias) ? $this->alias : 'AS ';

            if (preg_match('/\s+' . $alias . '\s*/', $data[$i])) {
                if (preg_match('/\w+\s+' . $alias . '\s*/', $data[$i])) {
                    $quoted = $this->endQuote . ' ' . $alias . $this->startQuote;
                    $data[$i] = str_replace(' ' . $alias, $quoted, $data[$i]);
                } else {
                    $quoted = $alias . $this->startQuote;
                    $data[$i] = str_replace($alias, $quoted, $data[$i]) . $this->endQuote;
                }
            }

            if (!empty($this->endQuote) && $this->endQuote == $this->startQuote) {
                if (substr_count($data[$i], $this->endQuote) % 2 == 1) {
                    if (substr($data[$i], -2) == $this->endQuote . $this->endQuote) {
                        $data[$i] = substr($data[$i], 0, -1);
                    } else {
                        $data[$i] = trim($data[$i], $this->endQuote);
                    }
                }
            }
            if (strpos($data[$i], '*')) {
                $data[$i] = str_replace($this->endQuote . '*' . $this->endQuote, '*', $data[$i]);
            }
            $data[$i] = str_replace($this->endQuote . $this->endQuote, $this->endQuote, $data[$i]);
        }

        return implode($data, ',');
    }

    /**
     * 转义数据库查询值
     * @access public
     * @param mixed  $data
     * @return mixed
     */
    public function value($data) {
        if (is_array($data) && !empty($data)) {
            return implode(',', array_map([$this, 'value'], $data));
        }
        if (is_numeric($data) && is_number_id($data)) {
            return $data;
        } elseif (is_null($data)) {
            return "''";
        } else {
            return $this->pdo->quote($data);
        }
    }

    /**
     * 最后插入行的ID或序列值
     * @return int
     */
    public function id() {
        if ($this->type == 'oracle') {
            return 0;
        } elseif ($this->type == 'mssql') {
            return $this->pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
        }
        return $this->pdo->lastInsertId();
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
        return $this->pdo->errorInfo();
    }

    /**
     * 最后一条SQl
     * @return string
     */
    public function last() {
        return end($this->logs);
    }

    /**
     * SQL日志
     * @return array
     */
    public function log() {
        return $this->logs;
    }

    /**
     * 返回服务器信息
     * @return array
     */
    public function info() {
        $output = [
            'server' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS'
        ];

        foreach ($output as $key => $value) {
            $output[$key] = @$this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }

        return $output;
    }

    /**
     * 启动事务
     */
    public function begin() {
        $this->pdo->beginTransaction();
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->pdo->rollBack();
    }

    /**
     * 提交事务
     */
    public function commit() {
        $this->pdo->commit();
    }

    /**
     * 执行事务
     * @param callable $actions
     * @return boolean
     */
    public function action($actions) {
        if (is_callable($actions)) {
            $this->beginTransaction();
            $result = $actions($this);
            if ($result === false) {
                $this->rollBack();
            } else {
                $this->commit();
            }
        } else {
            return false;
        }
    }

    /**
     * field分析
     * @access public
     * @param mixed $field
     * @return string
     */
    public function field($field) {
        if ('*' == $field || empty($field)) {
            $fieldStr = '*';
        } else {
            if (!is_array($field)) {
                $field = preg_split('/\s*,\s*/', trim($field), -1, PREG_SPLIT_NO_EMPTY);
            }
            foreach ($field as $i => $column) {
                if (strpos($column, '(') === false) {
                    if (preg_match('/^(.*?)\s+AS\s+(\w+)$/im', $column, $match)) {
                        $field[$i] = (strpos($match[1], '(') !== false ? $match[1] : $this->name($match[1])) . ' ' . $this->alias . ' ' . $this->name($match[2]);
                    } elseif (preg_match('/^(.*?)\s+\.\s+(\w+)$/im', $column, $match)) {
                        $field[$i] = (strpos($match[1], '(') === false ? $match[1] : $this->name($match[1])) . '.' . ($match[2] == '*' ? $match[2] : $this->name($match[2]));
                    } else {
                        $field[$i] = $this->name($column);
                    }
                }
            }
            $fieldStr = implode($field, ',');
        }
        return $fieldStr;
    }

    /**
     * data分析
     * @access public
     * @param mixed $data
     * @return string
     */
    public function data($data) {
        if (empty($data) || !is_array($data)) {
            return '';
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $va = [];
                foreach ($value as $k2 => $v2) {
                    $va[] = ($v2) ? $this->value($v2) : "''";
                }
                $v[] = '(' . implode(',', $va) . ')';
            } else {
                $v[] = ($value) ? $this->value($value) : "''";
            }
        }
        $data = !empty($v) ? implode(', ', $v) : '';
        $data = rtrim($data, ')');
        $data = ltrim($data, '(');
        return $data;
    }

    /**
     * table分析
     * @access public
     * @param mixed $table
     * @return string
     */
    public function table($tables, $alias = '') {
        if (empty($tables)) {
            return '';
        }
        if (!is_array($tables)) {
            $tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($tables as $i => $table) {
            if (strpos($table, '(') === false) {
                if (preg_match('/^(.*?)(?i:\s+as|)\s+([^ ]+)$/', $table, $matches)) {
                    $tables[$i] = $this->name($this->prefix . $matches[1]) . ' ' . $this->alias . ' ' . $this->name($matches[2]);
                } else {
                    $tables[$i] = $this->name($this->prefix . $table) . ($alias ? ' ' . $this->alias . ' ' . $this->name($alias) : '');
                }
            }
        }
        return implode(',', $tables);
    }

    /**
     * aliases分析
     * @access public
     * @param mixed $alias
     * @return string
     */
    public function aliases($alias) {
        return !empty($alias) ? $this->alias . $this->name($alias) : '';
    }

    /**
     * 生成并从数组生成一个JOIN语句
     * @access public
     * @param array $join
     * @return string
     */
    public function join($join) {
        if (!empty($join)) {
            $query = [];
            foreach ($join as $value) {
                $value = str_replace('#__PREFIX__#', $this->prefix, $value);
                $value = str_replace('#__CHARSET__#', $this->charset, $value);
                $query[] = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) {
                    return $this->name($this->prefix . strtolower($match[1]));
                }, $value);
            }
        }
        return isset($query) ? implode(' ', $query) : '';
    }

    /**
     * data分析
     * @access public
     * @param mixed $data
     * @return string
     */
    public function set($data) {
        if (empty($data) || !is_array($data)) {
            return '';
        }
        $k = [];
        foreach ($data as $key => $value) {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);
            if (isset($match[3]) && is_numeric($value)) {
                $k[] = $this->name($match[1]) . ' = ' . $this->name($match[1]) . $match[3] . $value;
            } elseif (isset($value[0]) && 'exp' == $value[0]) {
                $k[] = $this->name($key) . '=' . (string) $value[1];
            } elseif (is_numeric($key)) {
                $k[] = $key . ' = ' . $this->value($value);
            } else {
                $k[] = $this->name($key) . ' = ' . $this->value($value);
            }
        }
        return !empty($k) ? implode(',', $k) : '';
    }

    /**
     * where分析
     * @access public
     * @param mixed $where
     * @return string
     */
    public function where($where) {
        return $this->conditions($where);
    }

    /**
     * 创建GROUP BY SQL
     * @access public
     * @param string $group
     * @return mixed string
     */
    public function group($group) {
        if (!empty($group)) {
            if (is_array($group)) {
                $group = implode(', ', $group);
            }
            return ' GROUP BY ' . $this->quoteFields($group);
        }
    }

    /**
     * 创建ORDER BY
     * @access public
     * @param string $order 
     * @param string $direction  (ASC or DESC)
     * @return string 
     */
    public function order($order, $direction = 'ASC') {
        if (empty($order)) {
            return '';
        }
        if (!is_array($order)) {
            $order = preg_split('/\s*,\s*/', trim($order), -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach ($order as $i => $column) {
            if (strpos($column, '(') === false) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $order[$i] = $this->name($matches[1]) . ' ' . strtoupper($matches[2]);
                } else {
                    $order[$i] = $this->name($column) . ' ' . $direction;
                }
            }
        }
        return ' ORDER BY ' . implode(', ', $order);
    }

    /**
     * 处理LIMIT
     * @access public
     * @param int $limit  返回结果数
     * @param int $offset 开始结果
     * @param int $page 页码
     * @return string
     */
    public function limit($limit, $offset = null, $page = null) {
        if (!empty($limit)) {
            $rt = ' LIMIT ';
            if (is_number_id($offset)) {
                $rt .= $offset . ',';
            } elseif (is_number_id($page) && !$offset) {
                $rt .= intval($limit) * (intval($page) - 1) . ',';
            }
            $rt .= intval($limit);
            return $rt;
        }
    }

    public function offset($offset) {
        return is_number_id($offset) ? $offset : '';
    }

    public function columns($columns) {
        if (!empty($columns)) {
            $columns = implode(",\n\t", array_filter($columns)) . ',';
        }
        return !empty($columns) ? $columns : '';
    }

    public function indexes($indexes) {
        return !empty($indexes) ? "\t" . implode(",\n\t", array_filter($indexes)) : '';
    }

    /**
     * 通过解析条件
     * @access public
     * @param mixed $conditions  数组或条件字符串
     * @param bool  $quoteValues
     * @param bool  $where
     * @param Model $model 
     * @return string 
     */
    public function conditions($conditions, $quoteValues = true, $where = true, $model = null) {
        $clause = $out = '';
        if ($where) {
            $clause = ' WHERE ';
        }
        if (is_array($conditions) && !empty($conditions)) {
            $out = $this->conditionKeysToString($conditions);
            if (empty($out)) {
                return $clause . ' 1 = 1';
            }
            return $clause . $out;
        }
        if ($conditions === false || $conditions === true) {
            return $clause . (int) $conditions . ' = 1';
        }
        if (empty($conditions) || trim($conditions) == '') {
            return $clause . '1 = 1';
        }
        $clauses = '/^WHERE\\x20|^GROUP\\x20BY\\x20|^HAVING\\x20|^ORDER\\x20BY\\x20/i';
        if (preg_match($clauses, $conditions)) {
            $clause = '';
        }
        if (trim($conditions) == '') {
            $conditions = ' 1 = 1';
        } else {
            $conditions = $this->quoteFields($conditions);
        }
        return $clause . $conditions;
    }

    /**
     * 通过解析给定条件阵创建一个WHERE
     * @access public
     * @param array $conditions
     * @return string
     */
    public function conditionKeysToString($conditions, $join = ' AND ') {
        $out = [];
        foreach ($conditions as $key => $value) {
            $type = gettype($value);
            if (preg_match("/^(AND|OR)(\s+#.*)?$/i", $key, $match) && $type == 'array') {
                $out[] = '(' . $this->conditionKeysToString($value, ' ' . strtoupper($match[1]) . ' ') . ')';
            } else {
                if (is_numeric($key) && empty($value)) {
                    continue;
                } elseif (is_numeric($key) && is_string($value)) {
                    $out[] = $this->quoteFields($value);
                } elseif (is_numeric($key) && is_array($value)) {
                    $out[] = $this->conditionKeysToString($value, $join);
                } else {
                    $out[] = $this->parseKey(trim($key), $value);
                }
            }
        }
        return implode($join, $out);
    }

    /**
     * SQL条件运
     * @access public
     * @param string $key
     * @param mixed  $value
     * @return string
     */
    public function parseKey($key, $value) {
        preg_match('/(#?)([\w\.\-\|&]+)(\[(\>|\>\=|\<|\<\=|\!|\`|\<\>|\>\<|exp|\!?~)\])?/i', $key, $match);
        if (strpos($match[2], '|')) {
            $array = explode('|', $match[2]);
            $str = [];
            foreach ($array as $k) {
                $str[] = $this->parseKey($k . ($match[3] ?? ''), $value);
            }
            $operator = '( ' . implode(' OR ', $str) . ' )';
        } elseif (strpos($match[2], '&')) {
            $array = explode('&', $match[2]);
            $str = [];
            foreach ($array as $k) {
                $str[] = $this->parseKey($k . ($match[3] ?? ''), $value);
            }
            $operator = '( ' . implode(' AND ', $str) . ' )';
        } else {
            $type = gettype($value);
            $column = $this->name($match[2]);
            if (isset($match[4])) {
                $operator = $match[4];
                if ($operator == 'exp') {
                    $operator = $column . ' REGEXP ' . $this->value($value);
                } elseif ($operator == '!') {
                    switch ($type) {
                        case 'null':
                            $operator = $column . ' IS NOT NULL';
                            break;
                        case 'array':
                            $operator = $column . ' NOT IN (' . $this->value($value) . ')';
                            break;
                        case 'integer':
                        case 'double':
                            $operator = $column . ' != ' . $value;
                            break;
                        case 'boolean':
                            $operator = $column . ' != ' . ($value ? '1' : '0');
                            break;
                        case 'string':
                            $operator = $column . ' != ' . $this->value($value);
                            break;
                    }
                } elseif ($operator == '<>' || $operator == '><') {
                    if ($type == 'array') {
                        if ($operator == '><') {
                            $column .= ' NOT';
                        }
                        if (is_numeric($value[0]) && is_numeric($value[1])) {
                            $operator = '(' . $column . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                        } else {
                            $operator = '(' . $column . ' BETWEEN ' . $this->value($value[0]) . ' AND ' . $this->value($value[1]) . ')';
                        }
                    }
                } elseif ($operator == '~' || $operator == '!~') {
                    if ($type != 'array') {
                        $value = (array) $value;
                    }
                    $likeClauses = [];
                    foreach ($value as $item) {
                        $item = strval($item);
                        if (preg_match('/^(?!(%|\[|_])).+(?<!(%|\]|_))$/', $item)) {
                            $item = '%' . $item . '%';
                        }
                        $likeClauses[] = $column . ($operator === '!~' ? ' NOT' : '') . ' LIKE ' . $this->value($item);
                    }
                    $operator = implode(' OR ', $likeClauses);
                } elseif (in_array($operator, array('>', '>=', '<', '<='))) {
                    if (is_numeric($value)) {
                        $operator = $column . ' ' . $operator . ' ' . $value;
                    } else {
                        $operator = $column . ' ' . $operator . ' ' . $this->value($value);
                    }
                }
            } else {
                switch ($type) {
                    case 'null':
                        $operator = $column . ' IS NULL';
                        break;
                    case 'array':
                        $operator = $column . ' IN (' . $this->value($value) . ')';
                        break;
                    case 'integer':
                    case 'double':
                        $operator = $column . ' = ' . $value;
                        break;
                    case 'boolean':
                        $operator = $column . ' = ' . ($value ? 1 : 0);
                        break;
                    case 'string':
                        $operator = $column . ' = ' . $this->value($value);
                        break;
                }
            }
        }
        return $operator ?? '';
    }

    /**
     * 处理查询字段名称
     * @access public
     * @param string $conditions
     * @return string|false
     */
    public function quoteFields($conditions) {
        $start = $end = null;
        $original = $conditions;
        if (!empty($this->startQuote)) {
            $start = preg_quote($this->startQuote);
        }
        if (!empty($this->endQuote)) {
            $end = preg_quote($this->endQuote);
        }
        $conditions = str_replace([$start, $end], '', $conditions);
        preg_match_all('/(?:[\'\"][^\'\"\\\]*(?:\\\.[^\'\"\\\]*)*[\'\"])|([a-z0-9_' . $start . $end . ']*\\.[a-z0-9_' . $start . $end . ']*)/i', $conditions, $replace, PREG_PATTERN_ORDER);
        if (isset($replace['1']['0'])) {
            $pregCount = count($replace['1']);

            for ($i = 0; $i < $pregCount; ++$i) {
                if (!empty($replace['1'][$i]) && !is_numeric($replace['1'][$i])) {
                    $conditions = preg_replace('/\b' . preg_quote($replace['1'][$i]) . '\b/', $this->name($replace['1'][$i]), $conditions);
                }
            }
            return $conditions;
        }
        return $original;
    }

    /**
     * 生成并从数组生成SQL语句
     * @access public
     * @param array  $query
     * @param object $model 
     * @return string
     * */
    public function buildStatement($query, $table = '', $type = 'select') {
        //$query = @array_merge(['offset' => null, 'join' => []], $query);
        if (!empty($query['join'])) {
            $count = count($query['join']);
            for ($i = 0; $i < $count; ++$i) {
                if (is_array($query['join'][$i])) {
                    $query['join'][$i] = $this->buildJoinStatement($query['join'][$i]);
                }
            }
        }
        return $this->renderStatement($type, [
                    'replace' => $query['replace'] ?? false,
                    'field' => $this->field($query['field'] ?? ''),
                    'data' => $this->data($query['data'] ?? ''),
                    'table' => $this->table($query['table'] ?? $table),
                    'aliases' => $this->aliases($query['aliases'] ?? ''),
                    'join' => $this->join($query['join'] ?? ''),
                    'set' => $this->set($query['set'] ?? ''),
                    'where' => $this->where($query['where'] ?? ''),
                    'group' => $this->group($query['group'] ?? ''),
                    'order' => $this->order($query['order'] ?? ''),
                    'limit' => $this->limit($query['limit'] ?? '', $query['offset'] ?? '', $query['page'] ?? ''),
                    'offset' => $this->offset($query['offset'] ?? ''),
                    'columns' => $this->columns($query['columns'] ?? ''),
                    'indexes' => $this->indexes($query['indexes'] ?? '')
        ]);
    }

    /**
     * buildStatement别名
     * @access public
     * @return string
     * */
    public function buildQuery($query, $table = '', $type = 'select') {
        return $this->buildStatement($query, $table, $type);
    }

    private function switchSort($order) {
        $order = preg_replace('/\s+ASC/i', '__tmp_asc__', $order);
        $order = preg_replace('/\s+DESC/i', ' ASC', $order);
        return preg_replace('/__tmp_asc__/', ' DESC', $order);
    }

    /**
     * 呈现最终正确的顺序的SQL语句
     * @access public
     * @param string $type
     * @param array  $data
     * @return string
     */
    public function renderStatement($type, $data) {
        switch (strtolower($type)) {
            case 'select':
                if ($this->type === 'mssql') {
                    extract($data);
                    $field = trim($field);
                    if (strpos($limit, 'TOP') !== false && strpos($field, 'DISTINCT ') === 0) {
                        $limit = 'DISTINCT ' . trim($limit);
                        $field = substr($field, 9);
                    }
                    if (preg_match('/offset\s+([0-9]+)/i', $limit, $offset)) {
                        $limit = preg_replace('/\s*offset.*$/i', '', $limit);
                        preg_match('/top\s+([0-9]+)/i', $limit, $limitVal);
                        $offset = intval($offset[1]) + intval($limitVal[1]);
                        $rOrder = $this->switchSort($order);
                        list($order2, $rOrder) = [$this->mapFields($order), $this->mapFields($rOrder)];
                        $sql = str_replace(['%LIMIT%', '%OFFSET%', '%FIELD%', '%TABLE%', '%ALIASES%', '%JOIN%', '%WHERE%', '%GROUP%', '%ORDER%', '%RORDER%', '%ORDER2%'], [$limit ?? '', $offset ?? '', $field ?? '', $table ?? '', $alias ?? '', $joins ?? '', $conditions ?? '', $group ?? '', $order ?? '', $rOrder ?? '', $order2 ?? ''], $this->selectSql2);
                    } else {
                        $sql = str_replace(['%LIMIT%', '%FIELD%', '%TABLE%', '%ALIASES%', '%JOIN%', '%WHERE%', '%GROUP%', '%ORDER%'], [$limit ?? '', $field ?? '', $table ?? '', $alias ?? '', $joins ?? '', $conditions ?? '', $group ?? '', $order ?? ''], $this->selectSql);
                    }
                } else {
                    $sql = str_replace(['%FIELD%', '%TABLE%', '%ALIASES%', '%JOIN%', '%WHERE%', '%GROUP%', '%ORDER%', '%LIMIT%'], [$data['field'] ?? '*', $data['table'], $data['aliases'] ?? '', $data['join'] ?? '', $data['where'] ?? '', $data['group'] ?? '', $data['order'] ?? '', $data['limit'] ?? ''], $this->selectSql);
                }
                break;
            case 'create':
            case 'insert':
                $sql = str_replace(['%INSERT%', '%TABLE%', '%FIELD%', '%DATA%'], [!empty($data['replace']) ? 'REPLACE' : 'INSERT', $data['table'], $data['field'], $data['data'] ?? ''], $this->insertSql);
                break;
            case 'update':
                if ($this->type === 'mysql') {
                    $sql = str_replace(['%TABLE%', '%ALIASES%', '%SET%', '%WHERE%', '%LIMIT%'], [$data['table'], $data['aliases'] ?? '', $data['set'], $data['where'] ?? '', $data['limit'] ?? ''], $this->updateSql);
                } else {
                    $sql = str_replace(['%TABLE%', '%ALIASES%', '%SET%', '%WHERE%'], [$data['table'], $data['aliases'] ?? '', $data['set'], $data['where'] ?? ''], $this->updateSql);
                }
                break;
            case 'delete':
                $sql = str_replace(['%TABLE%', '%ALIASES%', '%WHERE%', '%LIMIT%'], [$data['table'] ?? '', $data['aliases'] ?? '', $data['where'] ?? '', $data['limit'] ?? ''], $this->deleteSql);
                break;
            case 'schema':
                if ($this->type === 'pgsql') {
                    $sql = str_replace(['%TABLE%', '%COLUMNS%', '%INDEXES%'], [$data['table'], $data['columns'] ?? '', $data['indexes'] ?? ''], $this->createTableSql);
                } elseif ($this->type === 'sqlite') {
                    $sql = 'CREATE TABLE ' . $data['table'] . " (\n" . $data['columns'] . ");\n{" . $data['indexes'] . '}';
                } else {
                    $sql = str_replace(['%TABLE%', '%COLUMNS%', '%INDEXES%'], [$data['table'], $data['columns'] ?? '', $data['indexes'] ?? ''], $this->createTableSql);
                }
                break;
        }
        return $sql;
        // return str_replace(['%INSERT%', '%FIELD%', '%DATA%', '%TABLE%', '%ALIASES%', '%JOIN%', '%SET%', '%WHERE%', '%GROUP%', '%ORDER%', '%LIMIT%', '%OFFSET%', '%COLUMNS%', '%INDEXES%'], [$data['replace'] ? 'REPLACE' : 'INSERT', $data['field'] ?? '', $data['data'] ?? '', $data['table'] ?? '', $data['aliases'] ?? '', $data['join'] ?? '', $data['set'] ?? '', $data['where'] ?? '', $data['group'] ?? '', $data['order'] ?? '', $data['limit'] ?? '', $data['offset'] ?? '', $data['columns'] ?? '', $data['indexes'] ?? ''], $sql);
    }

}
