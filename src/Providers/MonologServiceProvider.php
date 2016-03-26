<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

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
		// Register the PSR-3 processor
		$container->share(
			'monolog.processor.psr3',
			function ()
			{
				return new PsrLogMessageProcessor;
			}
		);

		// Register the web processor
		$container->share(
			'monolog.processor.web',
			function ()
			{
				return new WebProcessor;
			}
		);

		// Register the web application handler
		$container->share(
			'monolog.handler.application',
			function (Container $container)
			{
				/** @var \Joomla\Registry\Registry $config */
				$config = $container->get('config');

				$level = strtoupper($config->get('log.application', $config->get('log.level', 'error')));

				return new StreamHandler(
					APPROOT . '/logs/stats.log',
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
					APPROOT . '/logs/stats.log',
					constant('\\Monolog\\Logger::' . $level)
				);
			}
		);

		// Register the web application Logger
		$container->share(
			'monolog.logger.application',
			function (Container $container)
			{
				return new Logger(
					'Application',
					[
						$container->get('monolog.handler.application')
					],
					[
						$container->get('monolog.processor.web')
					]
				);
			}
		);

		// Register the CLI application Logger
		$container->share(
			'monolog.logger.cli',
			function (Container $container)
			{
				return new Logger(
					'Application',
					[
						$container->get('monolog.handler.application')
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
					'Database',
					[
						$container->get('monolog.handler.database')
					],
					[
						$container->get('monolog.processor.psr3'),
						$container->get('monolog.processor.web')
					]
				);
			}
		);
	}
}
