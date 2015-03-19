<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016 杭州博也网络科技, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Weixin\Model;
use Think\Model;

/**
 * mysql:
 * CREATE TABLE IF NOT EXISTS `boye_2015cjfx`.`cjfx_wxuser` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nickname` VARCHAR(32) NOT NULL COMMENT '昵称',
  `avatar` VARCHAR(255) NOT NULL COMMENT '头像地址',
  `referrer` INT NOT NULL DEFAULT 0 COMMENT '推荐人id，wxuser',
  `openid` VARCHAR(64) NOT NULL COMMENT '微信openid',
  `score` INT NOT NULL COMMENT '用户积分',
  `money` DECIMAL(16,2) NOT NULL COMMENT '账户余额',
  `costmoney` DECIMAL(16,2) NOT NULL COMMENT '消费金额',
  `createtime` INT NOT NULL COMMENT '数据创建时间',
  `updatetime` INT NOT NULL COMMENT '信息更新时间',
  `status` TINYINT NOT NULL COMMENT '数据状态,',
  `notes` VARCHAR(45) NOT NULL COMMENT '备注',
  `wxaccount_id` INT NOT NULL,
  `commission_id` INT NOT NULL,
  `wxuser_family_wxuserid` INT NOT NULL,
  `sex` TINYINT NOT NULL COMMENT '性别',
  `province` VARCHAR(32) NOT NULL COMMENT '省份',
  `city` VARCHAR(32) NOT NULL COMMENT '城市',
  `country` VARCHAR(32) NOT NULL COMMENT '国家',
  `subscribed` TINYINT(2) NOT NULL DEFAULT 1 COMMENT '是否关注公众号，1：是0：否',
  PRIMARY KEY (`id`),
  INDEX `fk_cjfx_wxuser_cjfx_wxaccount_idx` (`wxaccount_id` ASC),
  INDEX `fk_cjfx_wxuser_cjfx_commission1_idx` (`commission_id` ASC),
  INDEX `fk_cjfx_wxuser_cjfx_wxuser_family1_idx` (`wxuser_family_wxuserid` ASC),
  CONSTRAINT `fk_cjfx_wxuser_cjfx_wxaccount`
    FOREIGN KEY (`wxaccount_id`)
    REFERENCES `boye_2015cjfx`.`cjfx_wxaccount` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cjfx_wxuser_cjfx_commission1`
    FOREIGN KEY (`commission_id`)
    REFERENCES `boye_2015cjfx`.`cjfx_commission` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cjfx_wxuser_cjfx_wxuser_family1`
    FOREIGN KEY (`wxuser_family_wxuserid`)
    REFERENCES `boye_2015cjfx`.`cjfx_wxuser_family` (`wxuserid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
	///////////////////////////////////
	`nickname` VARCHAR(32) NOT NULL COMMENT '昵称',
  `avatar` VARCHAR(255) NOT NULL COMMENT '头像地址',
  `referrer` INT NOT NULL DEFAULT 0 COMMENT '推荐人id，wxuser',
  `openid` VARCHAR(64) NOT NULL COMMENT '微信openid',
  `wxaccount_id` INT NOT NULL,

 */


class WxuserModel extends Model{
	
	//自动验证
	protected $_validate = array(
		array('nickname','require','昵称必须！'),
		array('avatar','require','头像必须！'),
		array('referrer','require','推荐人必须！'),
		array('openid','require','openid参数必须！'),
		array('wxaccount_id','require','公众号ID参数必须！'),
		
		array('sex', 'require','性别必须！'), 
		array('subscribe_time', 'require','关注时间必须！'), 
		
	);
	
	//自动完成
	protected $_auto = array(
		array('subscribed', 1, self::MODEL_INSERT), 
		array('costmoney', 0, self::MODEL_INSERT), 
		array('money', 0, self::MODEL_INSERT), 
		array('updatetime', 'time', self::MODEL_BOTH,'function'), 
		array('createtime', NOW_TIME, self::MODEL_INSERT), 
		array('notes', '', self::MODEL_INSERT), 		
		array('score',0, self::MODEL_INSERT), 
		array('status', '1', self::MODEL_INSERT), 
	);
	
}
