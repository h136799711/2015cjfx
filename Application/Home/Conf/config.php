<?php
return array(
	//'配置项'=>'配置值',
	'DEFAULT_THEME'=>'default',
	'SESSION_PREFIX'=>'Home_',
    // 数据库配置
    'DB_TYPE'                   =>  'mysql',
    'DB_PORT'                   =>  '3306',
    'DB_PREFIX'                 =>  'cjfx_',
    'TMPL_PARSE_STRING'  =>array(
     	'__CDN__' => __ROOT__.'/Public/cdn', // 更改默认的/Public 替换规则
		'__JS__'     => __ROOT__.'/Public/'.MODULE_NAME.'/js', // 增加新的JS类库路径替换规则
     	'__CSS__'     => __ROOT__.'/Public/'.MODULE_NAME.'/css', // 增加新的JS类库路径替换规则
     	'__IMG__'     => __ROOT__.'/Public/'.MODULE_NAME.'/imgs', // 增加新的JS类库路径替换规则	
     
	),	
//  'WXPAY_CONFIG'=>array(
//		'appid'=>'wx58aea38c0796394d',
//		'appsecret'=>'',
//		'notifyurl'=>'http://20150508.itboye.com/index.php/Home/WxpayTest/notify',
//		'mchid'=>'',
//		'key'=>'755c9713b729cd82467ac592ded397ee',//在微信发送的邮件中查看,patenerkey
//		'jsapicallurl'=>'http://1.test.8raw.com/index.php/Home/WxpayTest/index?showwxpaytitle=1',
//		'sslcertpath'=>'/alidata/8rawcert/10027619/apiclient_cert.pem',
//		'sslkeypath'=>'/alidata/8rawcert/10027619/apiclient_cert.pem',
//	)
);