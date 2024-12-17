
CREATE TABLE `roa_tool_work_calendar` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sId` varchar(17) NOT NULL COMMENT '事件主题',
  `sSubject` varchar(255) NOT NULL COMMENT '事件主题',
  `sBeginDate` date DEFAULT NULL COMMENT '开始时间',
  `sBeginTime` time DEFAULT NULL,
  `sBeginMonth` tinyint(2) NOT NULL DEFAULT '0',
  `nRemind` tinyint(2) NOT NULL COMMENT '提醒',
  `sRemindDate` date DEFAULT NULL COMMENT '提醒-自定义时间',
  `sRemindDateDiff` varchar(2) NOT NULL COMMENT '提醒时间与开始时间的日期天数差值',
  `nRepeat` tinyint(2) NOT NULL COMMENT '重复',
  `sRepeatEndDate` date DEFAULT NULL COMMENT '重复事件-截止日期',
  `sRepeatExclusionDate` text NOT NULL COMMENT '重复事件-排除日期',
  `sNote` text NOT NULL COMMENT '备注',
  `sbelongUsrId` varchar(17) NOT NULL COMMENT '创建人',
  `sChglog` text NOT NULL,
  `nIsDel` tinyint(2) NOT NULL DEFAULT '0',
  `dtInsert` datetime DEFAULT NULL,
  `dtUpdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sId` (`sId`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='工具-工作日历';