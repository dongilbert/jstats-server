<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Providers;

use Joomla\Application as JoomlaApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\Input\{
	Cli, Input
};
use Joomla\StatsServer\{
	CliApplication, Console, Router, WebApplication
};
use Joomla\StatsServer\Commands as AppCommands;
use Joomla\StatsServer\Controllers\{
	DisplayControllerCreate, DisplayControllerGet, SubmitControllerCreate, SubmitControllerGet
};
use Joomla\StatsServer\Database\Migrations;
use Joomla\StatsServer\GitHub\GitHub;
use Joomla\StatsServer\Models\StatsModel;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use Psr\Log\LoggerInterface;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Application service provider
 */
class ApplicationServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
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
		$container->share(DisplayControllerCreate::class, [$this, 'getDisplayControllerCreateService'], true);
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
	 */
	public function getAnalyticsService(Container $container)
	{
		return new Analytics(true);
	}

	/**
	 * Get the CLI application service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CliApplication
	 */
	public function getCliApplicationService(Container $container) : CliApplication
	{
		$application = new CliApplication(
			$container->get(Cli::class),
			$container->get('config'),
			$container->get(JoomlaApplication\Cli\CliOutput::class),
			$container->get(JoomlaApplication\Cli\CliInput::class),
			$container->get(Console::class)
		);

		// Inject extra services
		$application->setLogger($container->get('monolog.logger.cli'));

		return $application;
	}

	/**
	 * Get the CliInput class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\CliInput
	 */
	public function getCliInputService(Container $container) : JoomlaApplication\Cli\CliInput
	{
		return new JoomlaApplication\Cli\CliInput;
	}

	/**
	 * Get the CliOutput class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\CliOutput
	 */
	public function getCliOutputService(Container $container) : JoomlaApplication\Cli\Output\Stdout
	{
		return new JoomlaApplication\Cli\Output\Stdout($container->get(JoomlaApplication\Cli\Output\Processor\ColorProcessor::class));
	}

	/**
	 * Get the ColorProcessor class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoomlaApplication\Cli\Output\Processor\ColorProcessor
	 */
	public function getColorProcessorService(Container $container) : JoomlaApplication\Cli\Output\Processor\ColorProcessor
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
	 */
	public function getConsoleService(Container $container) : Console
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
	 */
	public function getDatabaseMigrateCommandService(Container $container) : AppCommands\Database\MigrateCommand
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
	 */
	public function getDatabaseStatusCommandService(Container $container) : AppCommands\Database\StatusCommand
	{
		$command = new AppCommands\Database\StatusCommand($container->get(Migrations::class));

		$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$command->setInput($container->get(Input::class));

		return $command;
	}

	/**
	 * Get the DisplayControllerCreate class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DisplayControllerCreate
	 */
	public function getDisplayControllerCreateService(Container $container) : DisplayControllerCreate
	{
		$controller = new DisplayControllerCreate;

		$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
		$controller->setInput($container->get(Input::class));

		return $controller;
	}

	/**
	 * Get the DisplayControllerGet class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DisplayControllerGet
	 */
	public function getDisplayControllerGetService(Container $container) : DisplayControllerGet
	{
		$controller = new DisplayControllerGet(
			$container->get(StatsJsonView::class)
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
	 */
	public function getHelpCommandService(Container $container) : AppCommands\HelpCommand
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
	 */
	public function getInputCliService(Container $container) : Cli
	{
		return new Cli;
	}

	/**
	 * Get the Input class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Input
	 */
	public function getInputService(Container $container) : Input
	{
		return new Input($_REQUEST);
	}

	/**
	 * Get the InstallCommand class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AppCommands\InstallCommand
	 */
	public function getInstallCommandService(Container $container) : AppCommands\InstallCommand
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
	 */
	public function getRouterService(Container $container) : Router
	{
		$router = (new Router($container->get(Input::class)))
			->setControllerPrefix('Joomla\\StatsServer\\Controllers\\')
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
	 */
	public function getSnapshotCommandService(Container $container) : AppCommands\SnapshotCommand
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
	 */
	public function getStatsJsonViewService(Container $container) : StatsJsonView
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
	 */
	public function getStatsModelService(Container $container) : StatsModel
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
	 */
	public function getSubmitControllerCreateService(Container $container) : SubmitControllerCreate
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
	 */
	public function getSubmitControllerGetService(Container $container) : SubmitControllerGet
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
	 */
	public function getTagsJoomlaCommandService(Container $container) : AppCommands\Tags\JoomlaCommand
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
	 */
	public function getTagsPhpCommandService(Container $container) : AppCommands\Tags\PhpCommand
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
	 */
	public function getUpdateCommandService(Container $container) : AppCommands\UpdateCommand
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
	 */
	public function getWebApplicationService(Container $container) : WebApplication
	{
		$application = new WebApplication($container->get(Input::class), $container->get('config'));

		// Inject extra services
		$application->setAnalytics($container->get(Analytics::class));
		$application->setLogger($container->get('monolog.logger.application'));
		$application->setRouter($container->get(Router::class));

		return $application;
	}
}
