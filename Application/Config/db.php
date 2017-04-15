<?php

/**
 * 数据库配制
 */
return[
//    'type' => 'sqlite',
//    'file' => 'my/database/path/database.db',
    'type' => 'mysql', //数据库类型
    'name' => 'cookphp', //数据库名称
    'server' => '192.168.0.101', //服务器地址
    'username' => 'root', //账号
    'password' => '123', //密码
    'charset' => 'utf8', //编码
    'port' => 3306, //端口
    'prefix' => 'c_', //表前缀
    'socket' => '', // '/tmp/mysql.sock', //MySQL套接字（不能与服务器和端口一起使用）
//http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL
    ],
    //连接到数据库后，执行这些命令进行初始化
    'command' => [
    ]
];
