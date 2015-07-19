# Joomla Environment Stats

In order to better understand our install base and end user environments, this plugin has been created to send
those stats back to a Joomla controlled central server. No worries though, __no__ identifying data is captured
at any point, and most data is dumped every 12 hours, after analysis and stat storage.

## Installation

1. Clone this repo on your server into the folder **jstats-server**
2. Create a database called **jstats-server**
3. Create a table with this code
```
CREATE TABLE IF NOT EXISTS `jos_jstats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(32) NOT NULL,
  `php_version` varchar(255) NOT NULL,
  `db_type` varchar(255) NOT NULL,
  `db_version` varchar(255) NOT NULL,
  `cms_version` varchar(255) NOT NULL,
  `server_os` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
```

4. Run ```composer install``` from the jstats-server folder to install the dependencies
5. Run ```composer update``` from the jstats-server folder to update the lock file
6. Update the file etc/config.json with your database details if needed

## The Server

There are several requirements for running the JStats server

* mod_rewrite must be enabled
* AllowOverride All must be set in the Apache config in order for the .htaccess file to be used in the www folder
