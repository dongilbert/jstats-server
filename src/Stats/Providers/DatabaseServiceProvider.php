<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Database\DatabaseDriver;

class DatabaseServiceProvider implements ServiceProviderInterface
{
	public function register(Container $container)
	{
		$container->share(
			"Joomla\\Database\\DatabaseDriver",
			function () use ($container)
			{
				$config = $container->get("config");

				return DatabaseDriver::getInstance($config["database"]);
			},
			true
		);

		/**
		 * Until we release Joomla DI with alias support.
		 */
		$container->set(
			"db",
			function () use ($container)
			{
				return $container->get("Joomla\\Database\\DatabaseDriver");
			}
		);
	}
}
