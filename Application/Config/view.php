<?php

/*
 * 模板视图设置
 */
return[
    //定义视图模板解析左标示
    'left' => '{',
    //定义视图模板解析右标示
    'right' => '}',
    //定义视图模板文件后缀
    'tplsuffix' => '.tpl',
    //定义视图编译文件后缀
    'compilesuffix' => '.php',
    //定义视图缓存文件后缀
    'cachesuffix' => '.html',
    //定义视图模板是否运行插入PHP代码
    'php' => false,
    //定义视图模板是否持续编译
    'conver' => false,
    //定义视图模板是否压缩html
    'compresshtml' => true,
    //定义是否开启视图模板布局
    'layout' => false,
    //定义是否开启视图模板布局入口文件名
    'layoutname' => 'Public:layout',
    //定义视图模板输出替换变量
    'layoutitem' => '{__REPLACE__}',
    //替换标签
    'parsestring' => [
        '__ASSETS__' => url_base('/assets')
    ],
    //定义视图模板缓存时间(单位秒) 0永远缓存 缓存一个小时：3600
    'cachetime' => 0,
    //是否显示页面Trace信息
    'showtrace' => false,
    //视图模板样式
    'theme' => '',
    //编译目录
    'compiledir' => __CACHE__ . 'ViewCompile',
    //缓存目录
    'cachedir' => __TMP__ . 'ViewCache',
    //压缩等级 0-9
    'compression' => 9,
];

