
DROP TABLE IF EXISTS `tp_shop_page_theme`;

CREATE TABLE `tp_shop_page_theme` (
  `st_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `theme_type` varchar(20) DEFAULT '' COMMENT '模板类型',
  `select_theme` varchar(20) DEFAULT '' COMMENT '所选模板',
  `page` text COMMENT '首页排版',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`st_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

