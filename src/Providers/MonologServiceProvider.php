<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
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
		// Register the PSR-3 processor
		$container->share('monolog.processor.psr3', [$this, 'getMonologProcessorPsr3Service'], true);

		// Register the web processor
		$container->share('monolog.processor.web', [$this, 'getMonologProcessorWebService'], true);

		// Register the web application handler
		$container->share('monolog.handler.application', [$this, 'getMonologHandlerApplicationService'], true);

		// Register the database handler
		$container->share('monolog.handler.database', [$this, 'getMonologHandlerDatabaseService'], true);

		// Register the web application Logger
		$container->share('monolog.logger.application', [$this, 'getMonologLoggerApplicationService'], true);

		// Register the CLI application Logger
		$container->share('monolog.logger.cli', [$this, 'getMonologLoggerCliService'], true);

		// Register the database Logger
		$container->share('monolog.logger.database', [$this, 'getMonologLoggerDatabaseService'], true);
	}

	/**
	 * Get the `monolog.processor.psr3` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PsrLogMessageProcessor
	 *
	 * @since   1.0
	 */
	public function getMonologProcessorPsr3Service(Container $container) : PsrLogMessageProcessor
	{
		return new PsrLogMessageProcessor;
	}

	/**
	 * Get the `monolog.processor.web` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebProcessor
	 *
	 * @since   1.0
	 */
	public function getMonologProcessorWebService(Container $container) : WebProcessor
	{
		return new WebProcessor;
	}

	/**
	 * Get the `monolog.handler.application` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StreamHandler
	 *
	 * @since   1.0
	 */
	public function getMonologHandlerApplicationService(Container $container) : StreamHandler
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		$level = strtoupper($config->get('log.application', $config->get('log.level', 'error')));

		return new StreamHandler(APPROOT . '/logs/stats.log', constant('\\Monolog\\Logger::' . $level));
	}

	/**
	 * Get the `monolog.handler.database` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StreamHandler
	 *
	 * @since   1.0
	 */
	public function getMonologHandlerDatabaseService(Container $container) : StreamHandler
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		// If database debugging is enabled then force the logger's error level to DEBUG, otherwise use the level defined in the app config
		$level = $config->get('database.debug', false) ? 'DEBUG' : strtoupper($config->get('log.database', $config->get('log.level', 'error')));

		return new StreamHandler(APPROOT . '/logs/stats.log', constant('\\Monolog\\Logger::' . $level));
	}

	/**
	 * Get the `monolog.logger.application` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 *
	 * @since   1.0
	 */
	public function getMonologLoggerApplicationService(Container $container) : Logger
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

	/**
	 * Get the `monolog.logger.cli` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 *
	 * @since   1.0
	 */
	public function getMonologLoggerCliService(Container $container) : Logger
	{
		return new Logger(
			'Application',
			[
				$container->get('monolog.handler.application')
			]
		);
	}

	/**
	 * Get the `monolog.logger.database` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Logger
	 *
	 * @since   1.0
	 */
	public function getMonologLoggerDatabaseService(Container $container) : Logger
	{
		return new Logger(
			'Application',
			[
				$container->get('monolog.handler.database')
			],
			[
				$container->get('monolog.processor.psr3'),
				$container->get('monolog.processor.web')
			]
		);
	}
}
