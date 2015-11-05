<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Database\DatabaseDriver;

/**
 * Database service provider
 *
 * @since  1.0
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		$container->alias('db', 'Joomla\\Database\\DatabaseDriver')
			->share(
				'Joomla\\Database\\DatabaseDriver',
				function (Container $container)
				{
					$config = $container->get('config');

					$db = DatabaseDriver::getInstance((array) $config->get('database'));
					$db->setDebug($config->get('database.debug'));
					$db->setLogger($container->get('monolog.logger.database'));

					return $db;
				},
				true
			);
	}
}
