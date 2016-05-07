<?php

namespace Stats\Providers;

use Doctrine\Common\Cache\Cache;
use Joomla\Application as JoomlaApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Cli;
use Joomla\Input\Input;
use Stats\CliApplication;
use Stats\Commands\HelpCommand;
use Stats\Commands\JoomlaTagsCommand;
use Stats\Commands\SnapshotCommand;
use Stats\Console;
use Stats\Controllers\DisplayControllerGet;
use Stats\Controllers\SubmitControllerCreate;
use Stats\Controllers\SubmitControllerGet;
use Stats\GitHub\GitHub;
use Stats\Models\StatsModel;
use Stats\Router;
use Stats\Views\Stats\StatsJsonView;
use Stats\WebApplication;

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
		$container->alias(CliApplication::class, JoomlaApplication\AbstractCliApplication::class)
			->share(
				JoomlaApplication\AbstractCliApplication::class,
				function (Container $container)
				{
					$application = new CliApplication(
						$container->get(Cli::class),
						$container->get('config'),
						$container->get(JoomlaApplication\Cli\CliOutput::class),
						$container->get(Console::class)
					);

					// Inject extra services
					$application->setContainer($container);
					$application->setLogger($container->get('monolog.logger.cli'));

					return $application;
				},
				true
			);

		$container->alias(WebApplication::class, JoomlaApplication\AbstractWebApplication::class)
			->share(
				JoomlaApplication\AbstractWebApplication::class,
				function (Container $container)
				{
					$application = new WebApplication($container->get(Input::class), $container->get('config'));

					// Inject extra services
					$application->setLogger($container->get('monolog.logger.application'));
					$application->setRouter($container->get(Router::class));

					return $application;
				},
				true
			);

		$container->share(
			Input::class,
			function ()
			{
				return new Input($_REQUEST);
			},
			true
		);

		$container->share(
			Cli::class,
			function ()
			{
				return new Cli;
			},
			true
		);

		$container->share(
			Console::class,
			function (Container $container)
			{
				$console = new Console;
				$console->setContainer($container);

				return $console;
			}
		);

		$container->share(
			JoomlaApplication\Cli\Output\Processor\ColorProcessor::class,
			function (Container $container)
			{
				$processor = new JoomlaApplication\Cli\Output\Processor\ColorProcessor;

				/** @var Input $input */
				$input = $container->get(Cli::class);

				if ($input->get('nocolors'))
				{
					$processor->noColors = true;
				}

				// Setup app colors (also required in "nocolors" mode - to strip them).
				$processor->addStyle('title', new JoomlaApplication\Cli\ColorStyle('yellow', '', ['bold']));

				return $processor;
			}
		);

		$container->alias(JoomlaApplication\Cli\CliOutput::class, JoomlaApplication\Cli\Output\Stdout::class)
			->share(
				JoomlaApplication\Cli\Output\Stdout::class,
				function (Container $container)
				{
					return new JoomlaApplication\Cli\Output\Stdout($container->get(JoomlaApplication\Cli\Output\Processor\ColorProcessor::class));
				}
			);

		$container->share(
			Router::class,
			function (Container $container)
			{
				$router = (new Router($container->get(Input::class)))
					->setContainer($container)
					->setControllerPrefix('Stats\\Controllers\\')
					->setDefaultController('DisplayController')
					->addMap('/submit', 'SubmitController')
					->addMap('/:source', 'DisplayController');

				return $router;
			},
			true
		);

		$container->share(
			HelpCommand::class,
			function (Container $container)
			{
				$command = new HelpCommand;

				$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$command->setInput($container->get(Input::class));

				return $command;
			},
			true
		);

		$container->share(
			JoomlaTagsCommand::class,
			function (Container $container)
			{
				$command = new JoomlaTagsCommand($container->get(GitHub::class));

				$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$command->setInput($container->get(Input::class));

				return $command;
			},
			true
		);

		$container->share(
			SnapshotCommand::class,
			function (Container $container)
			{
				$command = new SnapshotCommand($container->get(StatsJsonView::class));

				$command->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$command->setInput($container->get(Input::class));

				return $command;
			},
			true
		);

		$container->share(
			DisplayControllerGet::class,
			function (Container $container)
			{
				$controller = new DisplayControllerGet(
					$container->get(StatsJsonView::class),
					$container->get(Cache::class)
				);

				$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$controller->setInput($container->get(Input::class));

				return $controller;
			},
			true
		);

		$container->share(
			SubmitControllerCreate::class,
			function (Container $container)
			{
				$controller = new SubmitControllerCreate(
					$container->get(StatsModel::class)
				);

				$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$controller->setInput($container->get(Input::class));

				return $controller;
			},
			true
		);

		$container->share(
			SubmitControllerGet::class,
			function (Container $container)
			{
				$controller = new SubmitControllerGet;

				$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$controller->setInput($container->get(Input::class));

				return $controller;
			},
			true
		);

		$container->share(
			StatsModel::class,
			function (Container $container)
			{
				return new StatsModel(
					$container->get(DatabaseDriver::class)
				);
			},
			true
		);

		$container->share(
			StatsJsonView::class,
			function (Container $container)
			{
				return new StatsJsonView(
					$container->get(StatsModel::class)
				);
			},
			true
		);
	}
}
