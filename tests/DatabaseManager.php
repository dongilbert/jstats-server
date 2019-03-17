<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\StatsServer\Database\Migrations;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Helper class for building a database connection in the test environment
 */
class DatabaseManager
{
	/**
	 * The database connection for the test environment
	 *
	 * @var  DatabaseInterface|null
	 */
	private static $connection;

	/**
	 * The database name for connections
	 *
	 * @var  string|null
	 */
	private static $dbName;

	/**
	 * The database connection parameters from the environment configuration
	 *
	 * @var  array
	 */
	private static $params = [];

	/**
	 * Clears the database tables of all data
	 *
	 * @return  void
	 */
	public static function clearTables(): void
	{
		$db = self::getConnection();

		foreach ($db->getTableList() as $table)
		{
			$db->truncateTable($table);
		}
	}

	/**
	 * Creates the database for the test environment
	 *
	 * @return  void
	 *
	 * @throws  DatabaseConnectionNotInitializedException
	 * @throws  ExecutionFailureException
	 */
	public static function createDatabase(): void
	{
		if (self::$connection === null)
		{
			throw new DatabaseConnectionNotInitializedException(
				sprintf(
					'The database connection has not been initialized, ensure you call %s::getConnection() first.',
					self::class
				)
			);
		}

		try
		{
			self::$connection->createDatabase(
				(object) [
					'db_name' => self::getDbName(),
					'db_user' => self::$params['user'],
				]
			);
		}
		catch (ExecutionFailureException $exception)
		{
			$stringToCheck = sprintf("Can't create database '%s'; database exists", self::getDbName());

			// If database exists, we're good
			if (strpos($exception->getMessage(), $stringToCheck) !== false)
			{
				return;
			}

			throw $exception;
		}
	}

	/**
	 * Destroys the database for the test environment
	 *
	 * @return  void
	 *
	 * @throws  DatabaseConnectionNotInitializedException
	 * @throws  ExecutionFailureException
	 */
	public static function dropDatabase(): void
	{
		if (self::$connection === null)
		{
			throw new DatabaseConnectionNotInitializedException(
				sprintf(
					'The database connection has not been initialized, ensure you call %s::getConnection() first.',
					self::class
				)
			);
		}

		try
		{
			self::$connection->setQuery('DROP DATABASE ' . self::$connection->quoteName(self::getDbName()))->execute();
		}
		catch (ExecutionFailureException $exception)
		{
			$stringToCheck = sprintf("Can't drop database '%s'; database doesn't exist", self::getDbName());

			// If database does not exist, we're good
			if (strpos($exception->getMessage(), $stringToCheck) !== false)
			{
				return;
			}

			throw $exception;
		}
	}

	/**
	 * Fetches the database driver, creating it if not yet set up
	 *
	 * @return  DatabaseInterface
	 */
	public static function getConnection(): DatabaseInterface
	{
		if (self::$connection === null)
		{
			self::initializeParams();
			self::createConnection();
		}

		return self::$connection;
	}

	/**
	 * Fetch the name of the database to use
	 *
	 * @return  string|null
	 */
	public static function getDbName(): ?string
	{
		return self::$dbName;
	}

	/**
	 * Load the example data into the database
	 *
	 * @return  void
	 */
	public static function loadExampleData(): void
	{
		$db = self::getConnection();

		$modifiedTimestamp = (new \DateTime('now', new \DateTimeZone('UTC')))->format($db->getDateFormat());

		$data = [
			[
				'php_version' => '5.5.38',
				'db_type'     => 'mysqli',
				'db_version'  => '5.6.41',
				'cms_version' => '3.9.4',
				'server_os'   => 'Darwin 14.1.0',
				'unique_id'   => 'a1b2c3d4',
				'modified'    => $modifiedTimestamp,
			],
			[
				'php_version' => '5.6.39',
				'db_type'     => 'mysql',
				'db_version'  => '5.7.26',
				'cms_version' => '3.9.2',
				'server_os'   => 'Windows NT 10.0',
				'unique_id'   => 'a2b3c4d5',
				'modified'    => $modifiedTimestamp,
			],
			[
				'php_version' => '7.0.33',
				'db_type'     => 'pdomysql',
				'db_version'  => '8.0.14',
				'cms_version' => '3.8.13',
				'server_os'   => 'Linux 4.14.68',
				'unique_id'   => 'a3b4c5d6',
				'modified'    => $modifiedTimestamp,
			],
			[
				'php_version' => '7.1.27',
				'db_type'     => 'pgsql',
				'db_version'  => '9.6.12',
				'cms_version' => '3.7.5',
				'server_os'   => 'FreeBSD 12.0-STABLE',
				'unique_id'   => 'a4b5c6d7',
				'modified'    => $modifiedTimestamp,
			],
			[
				'php_version' => '7.2.16',
				'db_type'     => 'postgresql',
				'db_version'  => '9.2.24',
				'cms_version' => '3.6.5',
				'server_os'   => 'OpenBSD 6.4',
				'unique_id'   => 'a5b6c7d8',
				'modified'    => $modifiedTimestamp,
			],
		];

		// Seed the main table first
		foreach ($data as $row)
		{
			$rowAsObject = (object) $row;

			$db->insertObject('#__jstats', $rowAsObject, ['unique_id']);
		}

		// Run the queries to seed the counter tables
		foreach (DatabaseDriver::splitSql(file_get_contents(APPROOT . '/etc/unatantum.sql')) as $query)
		{
			$query = trim($query);

			if (!empty($query))
			{
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Run the migrations to build the application database
	 *
	 * @return  void
	 */
	public static function runMigrations(): void
	{
		$db = self::getConnection();

		(new Migrations($db, new Filesystem(new Local(APPROOT . '/etc/migrations'))))->migrateDatabase();
	}

	/**
	 * Create the DatabaseDriver object
	 *
	 * @return  void
	 */
	private static function createConnection(): void
	{
		$params = self::$params;

		self::$connection = (new DatabaseFactory)->getDriver('mysql', self::$params);
	}

	/**
	 * Initialize the parameter storage for the database connection
	 *
	 * @return  void
	 *
	 * @throws  MissingDatabaseCredentialsException
	 */
	private static function initializeParams(): void
	{
		if (empty(self::$params))
		{
			$host     = getenv('JSTATS_DB_HOST');
			$user     = getenv('JSTATS_DB_USER');
			$password = getenv('JSTATS_DB_PASSWORD');
			$database = getenv('JSTATS_DB_DATABASE');
			$prefix   = getenv('JSTATS_DB_PREFIX');

			if (empty($host) || empty($user) || empty($password) || empty($database))
			{
				throw new MissingDatabaseCredentialsException;
			}

			self::$params = [
				'host'     => $host,
				'user'     => $user,
				'password' => $password,
				'prefix'   => $prefix,
			];

			self::$dbName = $database;
		}
	}
}
