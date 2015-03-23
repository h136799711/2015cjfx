<?php
/**
 * (c) Copyright 2014 hebidu. All Rights Reserved. 
 */
 

return array(
	//唯一管理员用户配置	
   'USER_ADMINISTRATOR' => 1, //管理员用户ID
   'MODULE_DENY_LIST'      =>  array('Common','Runtime'),
   'URL_CASE_INSENSITIVE' =>false,
	//程序版本
	//DONE:移到数据库中
	//显示运行时间
	'SHOW_RUN_TIME'=>true,
//	'SHOW_ADV_TIME'=>true,
	//显示数据库操作次数
//	'SHOW_DB_TIMES'=>true,
	//显示操作缓存次数
//	'SHOW_CACHE_TIMES'=>true,
	//显示使用内存
//	'SHOW_USE_MEM'=>true,
	//显示调用函数次数
//	'SHOW_FUN_TIMES'=>true,
	//伪静态配置
	'URL_HTML_SUFFIX'=>'shtml'	,
    // 路由配置
    'URL_MODEL'                 =>  1, // 如果你的环境不支持PATHINFO 请设置为3
    // 数据库配置
    'DB_TYPE'                   =>  'mysql',
    'DB_HOST'                   =>  'localhost',
    'DB_NAME'                   =>  'boye_2015cjfx', //微信api数据库
    'DB_USER'                   =>  'root',
    'DB_PWD'                    =>  '1',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'common_',
   //调试
    'LOG_RECORD' => true, // 开启日志记录
    'LOG_TYPE'              =>  'Db',
	'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误
    'LOG_DB_CONFIG'=>array(
		'dsn'=>'mysql://root:1@127.0.0.1:3306/boye_2015cjfx' //本地日志数据库
	),
    // Session 配置
    'SESSION_PREFIX' => 'oauth_',
    //权限配置
    'AUTH_CONFIG'=>array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'common_auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'common_auth_group_access', //用户组明细表
        'AUTH_RULE' => 'common_auth_rule', //权限规则表
        'AUTH_USER' => 'common_members'//用户信息表
    ),
    'WXPAY_CONFIG'=>array(
		'appid'=>'wx58aea38c0796394d',
		'appsecret'=>'3e1404c970566df55d7314ecfe9ff437',
		'notifyurl'=>'http://1.test.8raw.com/index.php/Home/WxpayTest/notify',
		'mchid'=>'10027619',//微信支付商户号  
		'key'=>'755c9713b729cd82467ac592ded397ee',//在微信发送的邮件中查看,patenerkey
		'jsapicallurl'=>'http://1.test.8raw.com/index.php/Home/WxpayTest/index',
		'sslcertpath'=>'',
		'sslkeypath'=>'',
	)
	
);
