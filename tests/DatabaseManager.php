<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\Database\DatabaseDriver;
use Joomla\StatsServer\Database\Migrations;
use Joomla\Test\DatabaseManager as BaseDatabaseManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Helper class for building a database connection in the test environment
 */
class DatabaseManager extends BaseDatabaseManager
{
	/**
	 * Load the example data into the database
	 *
	 * @return  void
	 */
	public function loadExampleData(): void
	{
		$db = $this->getConnection();

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
	public function runMigrations(): void
	{
		$db = $this->getConnection();

		(new Migrations($db, new Filesystem(new Local(APPROOT . '/etc/migrations'))))->migrateDatabase();
	}
}
