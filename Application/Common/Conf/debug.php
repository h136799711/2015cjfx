<?php
/**
 * (c) Copyright 2014 hebidu. All Rights Reserved. 
 */

 defined('THINK_PATH') or exit();
 
return  array(
    'LOG_TYPE'              =>  'Db',
    'LOG_RECORD'            =>  true,  // 进行日志记录
    'LOG_EXCEPTION_RECORD'  =>  true,    // 是否记录异常信息日志
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',//,WARN,NOTIC,INFO,DEBUG,SQL',  // 允许记录的日志级别
    'DB_FIELDS_CACHE'       =>  false, // 字段缓存信息
    'DB_SQL_LOG'            =>  true, // 记录SQL信息
    'TMPL_CACHE_ON'         =>  false,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_STRIP_SPACE'      =>  false,       // 是否去除模板文件里面的html空格与换行
    'SHOW_ERROR_MSG'        =>  true,    // 显示错误信息
    'URL_CASE_INSENSITIVE'  =>  false,  // URL区分大小写
    'SHOW_PAGE_TRACE'           =>  true, //显示调试信息
     // 数据库配置
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  'localhost',
    'DB_NAME'                   =>  'boye_2015cjfx',
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  '1',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'common_',
);