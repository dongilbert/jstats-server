# Joomla Environment Stats

In order to better understand our install base and end user environments, this plugin has been created to send
those stats back to a Joomla controlled central server. No worries though, __no__ identifying data is captured
at any point, and most data is dumped every 12 hours, after analysis and stat storage.

## Build Status
Travis-CI: [![Build Status](https://travis-ci.org/joomla/joomla-cms.png)](https://travis-ci.org/joomla/joomla-cms)

## Installation

1. Clone this repo on your server into the folder **jstats-server**
2. Create a database called **jstats-server**
3. Run the sql file https://github.com/joomla-extensions/jstats-server/blob/master/etc/mysql.sql
4. Run ```composer install``` from the jstats-server folder to install the dependencies
5. Update the file etc/config.json with your database details if needed

## The Server

There are several requirements for running the JStats server

* mod_rewrite must be enabled
* AllowOverride All must be set in the Apache config in order for the .htaccess file to be used in the www folder
