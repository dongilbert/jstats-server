<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Providers;

use Joomla\Console\Application;
use Joomla\Console\Loader\ContainerLoader;
use Joomla\Console\Loader\LoaderInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\StatsServer\Commands\Database\MigrateCommand;
use Joomla\StatsServer\Commands\Database\MigrationStatusCommand;
use Joomla\StatsServer\Database\Migrations;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Console service provider
 */
class ConsoleServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container): void
	{
		$container->share(Application::class, [$this, 'getConsoleApplicationService'], true);

		/*
		 * Application Helpers and Dependencies
		 */

		$container->alias(ContainerLoader::class, LoaderInterface::class)
			->share(LoaderInterface::class, [$this, 'getCommandLoaderService'], true);

		/*
		 * Commands
		 */

		$container->share(MigrateCommand::class, [$this, 'getDatabaseMigrateCommandService'], true);
		$container->share(MigrationStatusCommand::class, [$this, 'getDatabaseMigrationStatusCommandService'], true);
	}

	/**
	 * Get the LoaderInterface service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LoaderInterface
	 */
	public function getCommandLoaderService(Container $container): LoaderInterface
	{
		$mapping = [
			MigrationStatusCommand::getDefaultName() => MigrationStatusCommand::class,
			MigrateCommand::getDefaultName()         => MigrateCommand::class,
		];

		return new ContainerLoader($container, $mapping);
	}

	/**
	 * Get the console Application service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Application
	 */
	public function getConsoleApplicationService(Container $container): Application
	{
		$application = new Application(new ArgvInput, new ConsoleOutput, $container->get('config'));

		$application->setCommandLoader($container->get(LoaderInterface::class));
		$application->setDispatcher($container->get(DispatcherInterface::class));
		$application->setLogger($container->get(LoggerInterface::class));
		$application->setName('Joomla! Statistics Server');

		return $application;
	}

	/**
	 * Get the MigrateCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MigrateCommand
	 */
	public function getDatabaseMigrateCommandService(Container $container): MigrateCommand
	{
		$command = new MigrateCommand($container->get(Migrations::class));
		$command->setLogger($container->get(LoggerInterface::class));

		return $command;
	}

	/**
	 * Get the MigrationStatusCommand service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  MigrationStatusCommand
	 */
	public function getDatabaseMigrationStatusCommandService(Container $container): MigrationStatusCommand
	{
		return new MigrationStatusCommand($container->get(Migrations::class));
	}
}
