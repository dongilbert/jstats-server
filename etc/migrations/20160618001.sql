CREATE TABLE IF NOT EXISTS `#__jstats` (
  `unique_id` varchar(40) NOT NULL,
  `php_version` varchar(15) NOT NULL,
  `db_type` varchar(15) NOT NULL,
  `db_version` varchar(50) NOT NULL,
  `cms_version` varchar(15) NOT NULL,
  `server_os` varchar(255) NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`unique_id`),
  KEY `idx_php_version` (`php_version`),
  KEY `idx_db_type` (`db_type`),
  KEY `idx_db_version` (`db_version`),
  KEY `idx_cms_version` (`cms_version`),
  KEY `idx_server_os` (`server_os`),
  KEY `idx_database` (`db_type`, `db_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `#__migrations` (
  `version` varchar(25) NOT NULL COMMENT 'Applied migration versions',
  KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;
