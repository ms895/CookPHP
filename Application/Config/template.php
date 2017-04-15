<?php

/*
 * 模板视图设置
 */
return[
    //模板驱动
    'driver' => 'cook',
    //编译目录
    'compiledir' => __CACHE__ . 'ViewCompile',
    //缓存目录
    'cachedir' => __TMP__ . 'ViewCache',
    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'compilecache' => true,
    //压缩html空格
    'compresshtml' => true,
    //压缩等级 0-9
    'compression' => 9,
    //配制
    'config' => [
    ]
];

