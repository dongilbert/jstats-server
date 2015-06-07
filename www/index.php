<?php

require __DIR__ . "/../boot.php";

use Joomla\DI\Container;
use Stats\Providers\ConfigServiceProvider;
use Stats\Providers\DatabaseServiceProvider;
use Stats\Providers\TwigServiceProvider;

$container = (new Container)
	->registerServiceProvider(new ConfigServiceProvider(APPROOT . "/etc/config.json"))
	->registerServiceProvider(new DatabaseServiceProvider);

$app = $container->alias('app', 'Stats\Application')->buildObject('Stats\Application');
$container->registerServiceProvider(new TwigServiceProvider);
$app->setContainer($container);

$router = (new Stats\Router($app->input))
	->setControllerPrefix("Stats\\Controllers\\")
	->setDefaultController("DisplayController")
	->addMap("/submit", "SubmitController");

$app
	->setContainer($container)
	->setRouter($router)
	->execute();
