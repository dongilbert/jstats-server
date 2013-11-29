<?php

require __DIR__ . "/../boot.php";

use Joomla\DI\Container;
use Stats\Providers;

$config = include APPROOT . "/etc/config.php";

$container = (new Joomla\DI\Container)
	->registerServiceProvider(new Providers\ConfigServiceProvider($config))
	->registerServiceProvider(new Providers\DatabaseServiceProvider);


$app = (new Stats\Application)->setContainer($container);

$app->execute();
