<?php

require __DIR__ . "/../boot.php";

use Joomla\DI\Container;
use Stats\Providers\ConfigServiceProvider;
use Stats\Providers\DatabaseServiceProvider;

$container = (new Container)
	->registerServiceProvider(new ConfigServiceProvider(APPROOT . "/etc/config.php"))
	->registerServiceProvider(new DatabaseServiceProvider);

$app = new Stats\Application;

$router = (new Stats\Router($app->input))
	->setControllerPrefix("Stats\\Controllers\\")
	->setDefaultController("DefaultController")
	->addMap("/hi/:name", "HelloController")
	->addMap("/submit", "SubmitController");

$app
	->setContainer($container)
	->setRouter($router)
	->execute();
