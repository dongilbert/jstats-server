<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ConnectionFailureException;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for tests interacting with a database
 */
abstract class DatabaseTestCase extends TestCase
{
	/**
	 * The database connection for the test case
	 *
	 * @var  DatabaseInterface|null
	 */
	protected static $connection;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass(): void
	{
		try
		{
			$connection = DatabaseManager::getConnection();
			DatabaseManager::dropDatabase();
			DatabaseManager::createDatabase();
			$connection->select(DatabaseManager::getDbName());

			static::$connection = $connection;
		}
		catch (MissingDatabaseCredentialsException $exception)
		{
			static::markTestSkipped('Database credentials are not set, cannot run database tests.');
		}
		catch (ConnectionFailureException $exception)
		{
			static::markTestSkipped('Could not connect to the test database, cannot run database tests.');
		}
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass(): void
	{
		if (static::$connection !== null)
		{
			DatabaseManager::dropDatabase();
			static::$connection->disconnect();
		}
	}
}
