CREATE TABLE IF NOT EXISTS `#__jstats_counter_php_version` (
  `php_version` varchar(15) NOT NULL,
  `count` INT NOT NULL,
  PRIMARY KEY (`php_version`),
  KEY `idx_version_count` (`php_version`, `count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jstats_counter_db_version` (
  `db_version` varchar(50) NOT NULL,
  `count` INT NOT NULL,
  PRIMARY KEY (`db_version`),
  KEY `idx_version_count` (`db_version`, `count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jstats_counter_db_type` (
  `db_type` varchar(15) NOT NULL,
  `count` INT NOT NULL,
  PRIMARY KEY (`db_type`),
  KEY `idx_version_count` (`db_type`, `count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jstats_counter_cms_version` (
  `cms_version` varchar(15) NOT NULL,
  `count` INT NOT NULL,
  PRIMARY KEY (`cms_version`),
  KEY `idx_version_count` (`cms_version`, `count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jstats_counter_server_os` (
  `server_os` varchar(255) NOT NULL,
  `count` INT NOT NULL,
  PRIMARY KEY (`server_os`),
  KEY `idx_version_count` (`server_os`, `count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;
