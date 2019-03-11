<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;

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

	public static function getConnection(): DatabaseInterface
	{
		if (self::$connection === null)
		{
			self::initializeParams();
			self::createConnection();
		}

		return self::$connection;
	}

	public static function getDbName(): ?string
	{
		return self::$dbName;
	}

	private static function createConnection(): void
	{
		$params = self::$params;

		self::$connection = (new DatabaseFactory)->getDriver('mysql', self::$params);
	}

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
