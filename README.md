# Joomla Environment Stats

In order to better understand our install base and end user environments, a plugin has been created to send those stats back to a Joomla
controlled central server. No worries though, __no__ identifying data is captured at any point, and we only keep latest data last sent to us.

## Build Status
Travis-CI: [![Build Status](https://travis-ci.org/joomla-extensions/jstats-server.png)](https://travis-ci.org/joomla-extensions/jstats-server)
Scrutinizer-CI: [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/joomla-extensions/jstats-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/joomla-extensions/jstats-server/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/joomla-extensions/jstats-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/joomla-extensions/jstats-server/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/joomla-extensions/jstats-server/badges/build.png?b=master)](https://scrutinizer-ci.com/g/joomla-extensions/jstats-server/build-status/master)

## Requirements

* PHP 5.5+
* MySQL
* Composer
* Apache with mod_rewrite enabled and configured to allow the .htaccess file to be read

## Installation

1. Clone this repo on your web server
2. Create a database on your MySQL server
3. Copy `etc/config.dist.json` to `etc/config.json` and fill in your database credentials
4. Run the `composer install` command to install all dependencies
5. Run the `bin/stats install` command to create the application's database

## Additional Configuration

The `DisplayControllerGet` optionally supports several additional configuration values which affect the application's behavior, to include:

* Caching - The `doctrine/cache` package is used to provide a caching API to store data. The supported configuration values are under the `cache` key in the configuration and include:
    * `enabled` - Is the cache enabled?
    * `lifetime` - The lifetime (in seconds) of the cache data
    * `adapter` - The cache adapter to use; the currently supported values can be found in the [CacheServiceProvider](src/Providers/CacheServiceProvider.php) 
* Raw Data Access - The API supports requesting the raw, unfiltered API data by sending a `Joomla-Raw` with the API request. The value of this must match the `stats.rawdata` configuration key.

Additionally, the application behavior is affected by the following configuration settings:

* Error Reporting - The `errorReporting` configuration key can be set to a valid bitmask to be passed into the `error_reporting()` function
* Logging - The application's logging levels can be fine tuned by adjusting the `log` configuration keys:
    * `log.level` - The default logging level to use for all application loggers
    * `log.application` - The logging level to use specifically for the `monolog.handler.application` logger; defaults to the `log.level` value
    * `log.database` - The logging level to use specifically for the `monolog.handler.database` logger; defaults to the `log.level` value (Note: if `database.debug` is set to true then this level will ALWAYS correspond to the debug level)
