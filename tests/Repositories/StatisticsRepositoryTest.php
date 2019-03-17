<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Repositories;

use Joomla\Database\ParameterType;
use Joomla\StatsServer\Database\Exception\UnknownMigrationException;
use Joomla\StatsServer\Repositories\StatisticsRepository;
use Joomla\StatsServer\Tests\DatabaseManager;
use Joomla\StatsServer\Tests\DatabaseTestCase;

/**
 * Test class for \Joomla\StatsServer\Repositories\StatisticsRepository
 */
class StatisticsRepositoryTest extends DatabaseTestCase
{
	/**
	 * Repository class for testing
	 *
	 * @var  StatisticsRepository
	 */
	private $repository;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		DatabaseManager::runMigrations();
	}

	/**
	 * This method is called before each test.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		parent::setUp();

		DatabaseManager::loadExampleData();

		$this->repository = new StatisticsRepository(static::$connection);
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown(): void
	{
		DatabaseManager::clearTables();

		parent::tearDown();
	}

	/**
	 * @testdox The data for all tables is returned
	 */
	public function testTheDataForAllTablesIsReturned()
	{
		$data = $this->repository->getItems();

		foreach (StatisticsRepository::ALLOWED_SOURCES as $source)
		{
			$this->assertArrayHasKey($source, $data, sprintf('Missing data for "%s" source.', $source));
		}
	}

	/**
	 * @testdox The data for a single table is returned
	 */
	public function testTheDataForASingleTableIsReturned()
	{
		$data = $this->repository->getItems('cms_version');

		$this->assertNotEmpty($data);
	}

	/**
	 * @testdox The data is not fetched for an unknown source
	 */
	public function testTheDataIsNotFetchedForAnUnknownSource()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('An invalid data source was requested.');
		$this->expectExceptionCode(404);

		$data = $this->repository->getItems('does_not_exist');
	}

	/**
	 * @testdox The recently updated data for all tables is returned
	 */
	public function testTheRecentlyUpdatedDataForAllTablesIsReturned()
	{
		$data = $this->repository->getRecentlyUpdatedItems();

		foreach (StatisticsRepository::ALLOWED_SOURCES as $source)
		{
			$this->assertArrayHasKey($source, $data, sprintf('Missing data for "%s" source.', $source));
		}
	}

	/**
	 * @testdox A new row is saved to the database
	 */
	public function testANewRowIsSavedToTheDatabase()
	{
		$db = static::$connection;

		$id = 'unique-999';

		$row = (object) [
			'php_version' => PHP_VERSION,
			'db_type'     => static::$connection->getName(),
			'db_version'  => static::$connection->getVersion(),
			'cms_version' => '3.9.0',
			'server_os'   => php_uname('s') . ' ' . php_uname('r'),
			'unique_id'   => $id,
		];

		$this->repository->save($row);

		$rowFromDatabase = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from('#__jstats')
				->where('unique_id = :uniqueId')
				->bind('uniqueId', $id, ParameterType::STRING)
		)->loadObject();

		$this->assertNotNull($rowFromDatabase, 'The newly inserted row could not be queried.');

		$this->assertEquals($rowFromDatabase, $row, 'The database did not return the data that was inserted.');
		$this->assertObjectHasAttribute('modified', $rowFromDatabase, 'The modified timestamp should be included in the query result.');
	}

	/**
	 * @testdox An existing row is updated in the database
	 */
	public function testAnExistingRowIsUpdatedInTheDatabase()
	{
		$db = static::$connection;

		$id = 'a1b2c3d4';

		$row = (object) [
			'php_version' => PHP_VERSION,
			'db_type'     => static::$connection->getName(),
			'db_version'  => static::$connection->getVersion(),
			'cms_version' => '3.9.0',
			'server_os'   => php_uname('s') . ' ' . php_uname('r'),
			'unique_id'   => $id,
		];

		$this->repository->save($row);

		$rowFromDatabase = $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from('#__jstats')
				->where('unique_id = :uniqueId')
				->bind('uniqueId', $id, ParameterType::STRING)
		)->loadObject();

		$this->assertNotNull($rowFromDatabase, 'The updated row could not be queried.');

		$this->assertEquals($rowFromDatabase, $row, 'The database did not return the data that was updated.');
		$this->assertObjectHasAttribute('modified', $rowFromDatabase, 'The modified timestamp should be included in the query result.');
	}
}
