<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */
require \dirname(__DIR__) . '/boot.php';

use Joomla\Application\AbstractApplication;
use Joomla\Application\AbstractWebApplication;
use Joomla\Database\Service\DatabaseProvider;
use Joomla\DI\Container;
use Joomla\StatsServer\Providers\ApplicationServiceProvider;
use Joomla\StatsServer\Providers\ConfigServiceProvider;
use Joomla\StatsServer\Providers\DatabaseServiceProvider;
use Joomla\StatsServer\Providers\EventServiceProvider;
use Joomla\StatsServer\Providers\GitHubServiceProvider;
use Joomla\StatsServer\Providers\MonologServiceProvider;
use Monolog\ErrorHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

$container = (new Container)
	->registerServiceProvider(new ApplicationServiceProvider)
	->registerServiceProvider(new ConfigServiceProvider(APPROOT . '/etc/config.json'))
	->registerServiceProvider(new DatabaseProvider)
	->registerServiceProvider(new DatabaseServiceProvider)
	->registerServiceProvider(new EventServiceProvider)
	->registerServiceProvider(new GitHubServiceProvider)
	->registerServiceProvider(new MonologServiceProvider);

// Alias the web application to Joomla's base application class as this is the primary application for the environment
$container->alias(AbstractApplication::class, AbstractWebApplication::class);

// Alias the `monolog.logger.application` service to the Monolog Logger class and PSR-3 interface as this is the primary logger for the environment
$container->alias(Logger::class, 'monolog.logger.application')
	->alias(LoggerInterface::class, 'monolog.logger.application');

// Register deprecation logging via Monolog
ErrorHandler::register($container->get(Logger::class), [E_DEPRECATED, E_USER_DEPRECATED], false, false);

// Set error reporting based on config
$errorReporting = (int) $container->get('config')->get('errorReporting', 0);
error_reporting($errorReporting);
ini_set('display_errors', (bool) $errorReporting);

// Execute the application
try
{
	$container->get(AbstractApplication::class)->execute();
}
catch (\Throwable $e)
{
	if (!headers_sent())
	{
		header('HTTP/1.1 500 Internal Server Error', null, 500);
		header('Content-Type: application/json; charset=utf-8');
	}

	$response = [
		'error'   => true,
		'message' => 'An error occurred while executing the application: ' . $e->getMessage(),
	];

	echo json_encode($response);

	exit(1);
}
