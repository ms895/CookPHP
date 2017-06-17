<?php

/*
 * 缓存
 */
return[
    //缓存驱动
    'driver' => 'Redis',
    //缓存路径，文件时有效
    'path' => __CACHE__,
    //缓存服务器
    'host' => '192.168.0.101',//localhost
    //服务器端口
    'port' => 6379,
    //超时
    'timeout' => 10,
    //是否长连接
    'persistent' => true,
    //缓存前缀
    'prefix' => 'c_',
    //缓存时间
    'expire' => 3600,
    //开启数据校验
    'check' => false,
];
