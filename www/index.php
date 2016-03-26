<?php

require dirname(__DIR__) . '/boot.php';

use Joomla\Application\AbstractApplication;
use Joomla\Application\AbstractWebApplication;
use Joomla\DI\Container;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Stats\WebApplication;
use Stats\Providers\ApplicationServiceProvider;
use Stats\Providers\CacheServiceProvider;
use Stats\Providers\ConfigServiceProvider;
use Stats\Providers\DatabaseServiceProvider;
use Stats\Providers\MonologServiceProvider;

$container = (new Container)
	->registerServiceProvider(new ApplicationServiceProvider)
	->registerServiceProvider(new CacheServiceProvider)
	->registerServiceProvider(new ConfigServiceProvider(APPROOT . '/etc/config.json'))
	->registerServiceProvider(new DatabaseServiceProvider)
	->registerServiceProvider(new MonologServiceProvider);

// Alias the web application to Joomla's base application class as this is the primary application for the environment
$container->alias(AbstractApplication::class, AbstractWebApplication::class);

// Alias the `monolog.logger.application` service to the Monolog Logger class and PSR-3 interface as this is the primary logger for the environment
$container->alias(Logger::class, 'monolog.logger.application')
	->alias(LoggerInterface::class, 'monolog.logger.application');

// Set error reporting based on config
$errorReporting = (int) $container->get('config')->get('errorReporting', 0);
error_reporting($errorReporting);
ini_set('display_errors', (bool) $errorReporting);

// Execute the application
try
{
	$container->get(WebApplication::class)->execute();
}
catch (\Exception $e)
{
	if (!headers_sent())
	{
		header('HTTP/1.1 500 Internal Server Error', null, 500);
		header('Content-Type: application/json; charset=utf-8');
	}

	$response = [
		'error' => true,
		'message' => 'An error occurred while executing the application: ' . $e->getMessage()
	];

	echo json_encode($response);

	exit(1);
}
