<?php
/**
 * (c) Copyright 2014 hebidu. All Rights Reserved. 
 */
 

return array(
	'FAMILY_LEVEL'=>3,//分销级数
	'LOAD_EXT_CONFIG' => 'express', 
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
    'DB_HOST'                   =>  'rdsrrbifmrrbifm.mysql.rds.aliyuncs.com',
    'DB_NAME'                   =>  'boye_20150508', //微信api数据库
    'DB_USER'                   =>  'boye',
    'DB_PWD'                    =>  'bo-ye2015BO-YE',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'common_',
   //调试
    'LOG_RECORD' => true, // 开启日志记录
    'LOG_TYPE'              =>  'Db',
	'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误
    'LOG_DB_CONFIG'=>array(
		'dsn'=>'mysql://boye:bo-ye2015BO-YE@rdsrrbifmrrbifm.mysql.rds.aliyuncs.com:3306/boye_20150508' //本地日志数据库
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
		'appid'=>'wxada061ce2bd7f2c5',
		'appsecret'=>'0ce79e8ba1ecd7863d0a913fa45b11f9',
		'mchid'=>'1234849002',
		'notifyurl'=>'http://20150508.itboye.com/index.php/Shop/WxpayNotify/index',
		'key'=>'4EB050612582E9637C5E3FF497DCD8D9',//在微信发送的邮件中查看,patenerkey
		'jsapicallurl'=>'http://20150508.itboye.com/index.php/Shop/Orders/pay?showwxpaytitle=1',
		'sslcertpath'=>'',
		'sslkeypath'=>'',
	),
	'PROMOTIONCODE'=>array(
		'defaultQrcode'=>'./Uploads/QrcodeMerge/qrcode_default.jpg',
		'mergeFolder'=>'./Uploads/QrcodeMerge', //合并后的二维码存储位置
		'downloadFolder'=>'./Uploads/Qrcode',   //
		'noAuthorizedMsg'=>'您还未成为族长，不能生成专属二维码！', //
		'codeprefix'=>'UID_',//推广码所带前缀
		'tmpFolder'=>'./Temp',//临时文件夹可以删除里面的内容
		'bgImg'=>'./Uploads/QrcodeMerge/qrcode_template.jpg',//背景
	),
	'ROLE_ZUZHANG'=>11,
);
