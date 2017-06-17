<?php

/*
 * Session配制
 */
return[
    //是否默认启动Session
    'start' => true,
    //模块名称
    'module' => 'redis',
    //session cookie 的名称
    'name' => 'cook_session',
    //sess名称前缀
    'prefix' => '',
    //你希望 session 持续的秒数 如果你希望 session 不过期（直到浏览器关闭），将其设置为 0
    'expiration' => 7200,
    //Session指定存储位置，取决于使用的存储 session 的驱动
    //'path' => '',
    'path' => 'tcp://192.168.0.101:6379', //tcp://localhost:6379
];
