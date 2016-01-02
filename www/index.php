<?php

require dirname(__DIR__) . '/boot.php';

use Joomla\DI\Container;
use Stats\Application;
use Stats\Providers\ApplicationServiceProvider;
use Stats\Providers\ConfigServiceProvider;
use Stats\Providers\DatabaseServiceProvider;
use Stats\Providers\MonologServiceProvider;

$container = (new Container)
	->registerServiceProvider(new ApplicationServiceProvider)
	->registerServiceProvider(new ConfigServiceProvider(APPROOT . '/etc/config.json'))
	->registerServiceProvider(new DatabaseServiceProvider)
	->registerServiceProvider(new MonologServiceProvider);

// Set error reporting based on config
$errorReporting = (int) $container->get('config')->get('errorReporting', 0);
error_reporting($errorReporting);
ini_set('display_errors', (bool) $errorReporting);

// Execute the application
try
{
	$app = $container->get(Application::class);
	$app->execute();
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
