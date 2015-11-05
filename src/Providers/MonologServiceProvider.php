<?php
namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

/**
 * Monolog service provider
 *
 * @since  1.0
 */
class MonologServiceProvider implements ServiceProviderInterface
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
		// Register the web processor
		$container->share(
			'monolog.processor.web',
			function ()
			{
				return new WebProcessor;
			}
		);

		// Register the main application handler
		$container->share(
			'monolog.handler.application',
			function (Container $container)
			{
				/** @var \Joomla\Registry\Registry $config */
				$config = $container->get('config');

				$level = strtoupper($config->get('log.application', $config->get('log.level', 'error')));

				return new StreamHandler(
					APPROOT . '/logs/app.log',
					constant('\\Monolog\\Logger::' . $level)
				);
			}
		);

		// Register the database handler
		$container->share(
			'monolog.handler.database',
			function (Container $container)
			{
				/** @var \Joomla\Registry\Registry $config */
				$config = $container->get('config');

				// If database debugging is enabled then force the logger's error level to DEBUG, otherwise use the level defined in the app config
				$level = $config->get('database.debug', false) ? 'DEBUG' : strtoupper($config->get('log.database', $config->get('log.level', 'error')));

				return new StreamHandler(
					APPROOT . '/logs/database.log',
					constant('\\Monolog\\Logger::' . $level)
				);
			}
		);

		// Register the main Logger
		$container->alias('monolog', 'Monolog\\Logger')
			->alias('monolog.logger.application', 'Monolog\\Logger')
			->alias('Psr\\Log\\LoggerInterface', 'Monolog\\Logger')
			->share(
				'Monolog\\Logger',
				function (Container $container)
				{
					return new Logger(
						'MauticDashboard',
						[
							$container->get('monolog.handler.application')
						],
						[
							$container->get('monolog.processor.web')
						]
					);
				}
			);

		// Register the database Logger
		$container->share(
			'monolog.logger.database',
			function (Container $container)
			{
				return new Logger(
					'MauticDashboard',
					[
						$container->get('monolog.handler.database')
					],
					[
						$container->get('monolog.processor.web')
					]
				);
			}
		);
	}
}
