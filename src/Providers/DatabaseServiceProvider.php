<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Providers;

use Joomla\Database\DatabaseDriver;
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Joomla\StatsServer\Database\Migrations;

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
		$container->alias('db', DatabaseDriver::class)
			->share(DatabaseDriver::class, [$this, 'getDbService'], true);

		$container->alias('db.migrations', Migrations::class)
			->share(Migrations::class, [$this, 'getDbMigrationsService'], true);
	}

	/**
	 * Get the `db` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DatabaseDriver
	 *
	 * @since   1.0
	 */
	public function getDbService(Container $container) : DatabaseDriver
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		$db = DatabaseDriver::getInstance((array) $config->get('database'));
		$db->setDebug($config->get('database.debug'));
		$db->setLogger($container->get('monolog.logger.database'));

		return $db;
	}

	/**
	 * Get the `db.migrations` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Migrations
	 *
	 * @since   1.0
	 */
	public function getDbMigrationsService(Container $container) : Migrations
	{
		return new Migrations(
			$container->get('db'),
			new Filesystem(new Local(APPROOT . '/etc'))
		);
	}
}
