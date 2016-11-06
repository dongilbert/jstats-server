<?php

namespace Stats\Providers;

use Joomla\Application as JoomlaApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Cli;
use Joomla\Input\Input;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Stats\CliApplication;
use Stats\Commands as AppCommands;
use Stats\Console;
use Stats\Controllers\DisplayControllerGet;
use Stats\Controllers\SubmitControllerCreate;
use Stats\Controllers\SubmitControllerGet;
use Stats\Database\Migrations;
use Stats\GitHub\GitHub;
use Stats\Models\StatsModel;
use Stats\Router;
use Stats\Views\Stats\StatsJsonView;
use Stats\WebApplication;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Application service provider
 *
 * @since  1.0
 */
class ApplicationServiceProvider implements ServiceProviderInterface
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
		/*
		 * Application Classes
		 */

		$container->alias(CliApplication::class, JoomlaApplication\AbstractCliApplication::class)
			->share(JoomlaApplication\AbstractCliApplication::class, [$this, 'getCliApplicationService'], true);

		$container->alias(WebApplication::class, JoomlaApplication\AbstractWebApplication::class)
			->share(JoomlaApplication\AbstractWebApplication::class, [$this, 'getWebApplicationService'], true);

		/*
		 * Application Class Dependencies
		 */

		$container->share(Analytics::class, [$this, 'getAnalyticsService'], true);
		$container->share(Cli::class, [$this, 'getInputCliService'], true);
		$container->share(Console::class, [$this, 'getConsoleService'], true);
		$container->share(Input::class, [$this, 'getInputService'], true);
		$container->share(JoomlaApplication\Cli\Output\Processor\ColorProcessor::class, [$this, 'getColorProcessorService'], true);
		$container->share(JoomlaApplication\Cli\CliInput::class, [$this, 'getCliInputService'], true);
		$container->share(Router::class, [$this, 'getRouterService'], true);

		$container->alias(JoomlaApplication\Cli\CliOutput::class, JoomlaApplication\Cli\Output\Stdout::class)
			->share(JoomlaApplication\Cli\Output\Stdout::class, [$this, 'getCliOutputService'], true);

		/*
		 * Console Commands
		 */

		$container->share(AppCommands\Cache\ClearCommand::class, [$this, 'getCacheClearCommandService'], true);
		$container->share(AppCommands\HelpCommand::class, [$this, 'getHelpCommandService'], true);
		$container->share(AppCommands\InstallCommand::class, [$this, 'getInstallCommandService'], true);
		$container->share(AppCommands\Database\MigrateCommand::class, [$this, 'getDatabaseMigrateCommandService'], true);
		$container->share(AppCommands\Database\StatusCommand::class, [$this, 'getDatabaseStatusCommandService'], true);
		$container->share(AppCommands\SnapshotCommand::class, [$this, 'getSnapshotCommandService'], true);
		$container->share(AppCommands\Tags\JoomlaCommand::class, [$this, 'getTagsJoomlaCommandService'], true);
		$container->share(AppCommands\Tags\PhpCommand::class, [$this, 'getTagsPhpCommandService'], true);
		$container->share(AppCommands\UpdateCommand::class, [$this, 'getUpdateCommandService'], true);

		/*
		 * MVC Layer
		 */

		// Controllers
		$container->share(DisplayControllerGet::class, [$this, 'getDisplayControllerGetService'], true);
		$container->share(SubmitControllerCreate::class, [$this, 'getSubmitControllerCreateService'], true);
		$container->share(SubmitControllerGet::class, [$this, 'getSubmitControllerGetService'], true);

		// Models
		$container->share(StatsModel::class, [$this, 'getStatsModelService'], true);

		// Views
		$container->share(StatsJsonView::class, [$this, 'getStatsJsonViewService'], true);
	}

	/**
	 * Get the Analytics class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Analytics
	 *
	 * @since   1.0
	 */
	public function getAnalyticsService(Container $container)
	{
		return new Analytics(true);
	}

	/**
	 * Get the Cache\ClearCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\Cache\ClearCommand
	 *
	 * @since   1.0
	 */
	public function getCacheClearCommandService(Container $container)
	{
		$command = new AppCommands\Cache\ClearCommand($container->get(CacheItemPoolInterface::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the CLI application service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CliApplication
	 *
	 * @since   1.0
	 */
	public function getCliApplicationService(Container $container)
	{
		$application = new CliApplication(
			$container->get(Cli::class),
			$container->get('config'),
			$container->get(JoomlaApplication\Cli\CliOutput::class),
			$container->get(JoomlaApplication\Cli\CliInput::class),
			$container->get(Console::class)
		);

		// Inject extra services
		$application->setContainer($container);
		$application->setLogger($container->get('monolog.logger.cli'));

		return $application;
	}

	/**
	 * Get the CliInput class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\CliInput
	 *
	 * @since   1.0
	 */
	public function getCliInputService(Container $container)
	{
		return new JoomlaApplication\Cli\CliInput;
	}

	/**
	 * Get the CliOutput class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\CliOutput
	 *
	 * @since   1.0
	 */
	public function getCliOutputService(Container $container)
	{
		return new JoomlaApplication\Cli\Output\Stdout($container->get(JoomlaApplication\Cli\Output\Processor\ColorProcessor::class));
	}

	/**
	 * Get the ColorProcessor class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\Output\Processor\ColorProcessor
	 *
	 * @since   1.0
	 */
	public function getColorProcessorService(Container $container)
	{
		$processor = new JoomlaApplication\Cli\Output\Processor\ColorProcessor;

		/** @var Input $input */
		$input = $container->get(Cli::class);

		if ($input->getBool('nocolors', false))
		{
			$processor->noColors = true;
		}

		// Setup app colors (also required in "nocolors" mode - to strip them).
		$processor->addStyle('title', new JoomlaApplication\Cli\ColorStyle('yellow', '', ['bold']));

		return $processor;
	}

	/**
	 * Get the console service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Console
	 *
	 * @since   1.0
	 */
	public function getConsoleService(Container $container)
	{
		$console = new Console;
		$console->setContainer($container);

		return $console;
	}

	/**
	 * Get the Database\MigrateCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\Database\MigrateCommand
	 *
	 * @since   1.0
	 */
	public function getDatabaseMigrateCommandService(Container $container)
	{
		$command = new AppCommands\Database\MigrateCommand($container->get(Migrations::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));
		$command->setLogger($container->get(LoggerInterface::class));

		return $command;
	}

	/**
	 * Get the Database\StatusCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\Database\StatusCommand
	 *
	 * @since   1.0
	 */
	public function getDatabaseStatusCommandService(Container $container)
	{
		$command = new AppCommands\Database\StatusCommand($container->get(Migrations::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the DisplayControllerGet class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DisplayControllerGet
	 *
	 * @since   1.0
	 */
	public function getDisplayControllerGetService(Container $container)
	{
		$controller = new DisplayControllerGet(
			$container->get(StatsJsonView::class),
			$container->get(CacheItemPoolInterface::class)
		);

		$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$controller->setInput($container->get(Input::class));

		return $controller;
	}

	/**
	 * Get the HelpCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\HelpCommand
	 *
	 * @since   1.0
	 */
	public function getHelpCommandService(Container $container)
	{
		$command = new AppCommands\HelpCommand;

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the Input\Cli class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Cli
	 *
	 * @since   1.0
	 */
	public function getInputCliService(Container $container)
	{
		return new Cli;
	}

	/**
	 * Get the Input class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Input
	 *
	 * @since   1.0
	 */
	public function getInputService(Container $container)
	{
		return new Input($_REQUEST);
	}

	/**
	 * Get the InstallCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\InstallCommand
	 *
	 * @since   1.0
	 */
	public function getInstallCommandService(Container $container)
	{
		$command = new AppCommands\InstallCommand($container->get(DatabaseDriver::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the router service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Router
	 *
	 * @since   1.0
	 */
	public function getRouterService(Container $container)
	{
		$router = (new Router($container->get(Input::class)))
			->setControllerPrefix('Stats\\Controllers\\')
			->setDefaultController('DisplayController')
			->addMap('/submit', 'SubmitController')
			->addMap('/:source', 'DisplayController');

		$router->setContainer($container);

		return $router;
	}

	/**
	 * Get the SnapshotCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\SnapshotCommand
	 *
	 * @since   1.0
	 */
	public function getSnapshotCommandService(Container $container)
	{
		$command = new AppCommands\SnapshotCommand($container->get(StatsJsonView::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the StatsJsonView class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatsJsonView
	 *
	 * @since   1.0
	 */
	public function getStatsJsonViewService(Container $container)
	{
		return new StatsJsonView(
			$container->get(StatsModel::class)
		);
	}

	/**
	 * Get the StatsModel class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatsModel
	 *
	 * @since   1.0
	 */
	public function getStatsModelService(Container $container)
	{
		return new StatsModel(
			$container->get(DatabaseDriver::class)
		);
	}

	/**
	 * Get the SubmitControllerCreate class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SubmitControllerCreate
	 *
	 * @since   1.0
	 */
	public function getSubmitControllerCreateService(Container $container)
	{
		$controller = new SubmitControllerCreate(
			$container->get(StatsModel::class)
		);

		$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$controller->setInput($container->get(Input::class));

		return $controller;
	}

	/**
	 * Get the SubmitControllerGet class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SubmitControllerGet
	 *
	 * @since   1.0
	 */
	public function getSubmitControllerGetService(Container $container)
	{
		$controller = new SubmitControllerGet;

		$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$controller->setInput($container->get(Input::class));

		return $controller;
	}

	/**
	 * Get the Tags\JoomlaCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\Tags\JoomlaCommand
	 *
	 * @since   1.0
	 */
	public function getTagsJoomlaCommandService(Container $container)
	{
		$command = new AppCommands\Tags\JoomlaCommand($container->get(GitHub::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the Tags\PhpCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\Tags\PhpCommand
	 *
	 * @since   1.0
	 */
	public function getTagsPhpCommandService(Container $container)
	{
		$command = new AppCommands\Tags\PhpCommand($container->get(GitHub::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the UpdateCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\UpdateCommand
	 *
	 * @since   1.0
	 */
	public function getUpdateCommandService(Container $container)
	{
		$command = new AppCommands\UpdateCommand;

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the web application service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebApplication
	 *
	 * @since   1.0
	 */
	public function getWebApplicationService(Container $container)
	{
		$application = new WebApplication($container->get(Input::class), $container->get('config'));

		// Inject extra services
		$application->setAnalytics($container->get(Analytics::class));
		$application->setLogger($container->get('monolog.logger.application'));
		$application->setRouter($container->get(Router::class));

		return $application;
	}
}
