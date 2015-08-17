<?php

require __DIR__ . '/../boot.php';

use Joomla\DI\Container;
use Stats\Providers\ConfigServiceProvider;
use Stats\Providers\DatabaseServiceProvider;

$container = (new Container)
	->registerServiceProvider(new ConfigServiceProvider(APPROOT . '/etc/config.json'))
	->registerServiceProvider(new DatabaseServiceProvider);

$app = $container->alias('app', 'Stats\Application')->buildObject('Stats\Application');
$app->setContainer($container);

$router = (new Stats\Router($app->input))
	->setApplication($app)
	->setControllerPrefix('Stats\\Controllers\\')
	->setDefaultController('DisplayController')
	->addMap('/submit', 'SubmitController');

$app
	->setContainer($container)
	->setRouter($router)
	->execute();
