<?php

require __DIR__ . "/../boot.php";

use Joomla\DI\Container;
use Stats\Providers;

$container = (new Joomla\DI\Container)
	->registerServiceProvider(new Providers\ConfigServiceProvider(APPROOT . "/etc/config.php"))
	->registerServiceProvider(new Providers\DatabaseServiceProvider);


$app = (new Stats\Application)->setContainer($container);

$app->execute();
