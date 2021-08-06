CREATE TABLE `cronjob_functions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleName` varchar(255) DEFAULT NULL,
  `functionName` varchar(255) DEFAULT NULL,
  `cronTab` varchar(30) DEFAULT '* * * * *',
  `parent` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `status` varchar(15) DEFAULT 'active',
  `lastTry` datetime DEFAULT NULL,
  `lastDuration` varchar(10) DEFAULT NULL,
  `lastError` text DEFAULT NULL,
  `lastErrorDate` datetime DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cronjob_functions_id_index` (`id`),
  KEY `cronjob_functions_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8


CREATE TABLE `jk_lang_defined` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `tableName` varchar(40) DEFAULT NULL,
  `lang` varchar(3) DEFAULT NULL,
  `varCol` varchar(30) DEFAULT NULL,
  `var` varchar(255) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `jk_lang_defined_ID_index` (`ID`),
  KEY `jk_lang_defined_tableName_lang_varCol_var_index` (`tableName`,`lang`,`varCol`,`var`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8



CREATE TABLE `jk_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jk_options_name_uindex` (`name`),
  KEY `jk_options_id_index` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8



CREATE TABLE `jk_translate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `var` varchar(255) DEFAULT NULL,
  `lang` varchar(3) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `dest` varchar(40) DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `translate_id_uindex` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8


CREATE TABLE `module_installed_self` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `info` text NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `tables` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

CREATE TABLE `jk_listeners_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventID` int(11) DEFAULT NULL,
  `listener` varchar(255) DEFAULT NULL,
  `try` int(11) DEFAULT NULL,
  `result` int(11) DEFAULT NULL,
  `inputs` text DEFAULT NULL,
  `errors` text DEFAULT NULL,
  `expire` varchar(250) DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `registerTime` varchar(255) DEFAULT NULL,
  `lastErrorDate` datetime DEFAULT NULL,
  `lastTryTime` datetime DEFAULT NULL,
  `lastTryDuration` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

CREATE TABLE `jk_events_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) DEFAULT NULL,
  `after` int(11) DEFAULT NULL,
  `try` int(11) DEFAULT NULL,
  `result` int(11) DEFAULT NULL,
  `inputs` text DEFAULT NULL,
  `errors` text DEFAULT NULL,
  `expire` varchar(250) DEFAULT NULL,
  `registerTime` varchar(255) DEFAULT NULL,
  `tryAfter` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8


