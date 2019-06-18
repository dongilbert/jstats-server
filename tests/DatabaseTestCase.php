<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\Test\DatabaseManager as BaseDatabaseManager;
use Joomla\Test\DatabaseTestCase as BaseDatabaseTestCase;

/**
 * Extended test case for tests interacting with a database
 */
abstract class DatabaseTestCase extends BaseDatabaseTestCase
{
	/**
	 * The database manager
	 *
	 * @var  DatabaseManager|null
	 */
	protected static $dbManager;

	/**
	 * Create the database manager for this test class.
	 *
	 * If necessary, this method can be extended to create your own subclass of the base DatabaseManager object to customise
	 * the behaviors in your application.
	 *
	 * @return  DatabaseManager
	 */
	protected static function createDatabaseManager(): BaseDatabaseManager
	{
		return new DatabaseManager;
	}
}
