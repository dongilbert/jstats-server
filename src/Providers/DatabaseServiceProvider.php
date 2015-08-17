<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Database\DatabaseDriver;

class DatabaseServiceProvider implements ServiceProviderInterface
{
	public function register(Container $container)
	{
		$container->alias('db', 'Joomla\\Database\\DatabaseDriver')
			->share(
				'Joomla\\Database\\DatabaseDriver',
				function () use ($container)
				{
					$config = $container->get('config');

					return DatabaseDriver::getInstance((array) $config['database']);
				},
				true
			);
	}
}
