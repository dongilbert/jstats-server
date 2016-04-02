<?php

namespace Stats\Tests\Models;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Stats\Models\StatsModel;

/**
 * Test class for \Stats\Models\StatsModel
 */
class StatsModelTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The model returns all items from the database
	 *
	 * @covers  Stats\Models\StatsModel::getItems
	 */
	public function testTheModelReturnsAllItemsFromTheDatabase()
	{
		$return = [(object) ['unique_id' => '1a'], (object) ['unique_id' => '2b']];

		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'loadObjectList', 'loadResult'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder(DatabaseQuery::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->exactly(2))
			->method('getQuery')
			->willReturn($mockQuery);

		$mockDatabase->expects($this->once())
			->method('loadObjectList')
			->willReturn($return);

		$mockDatabase->expects($this->once())
			->method('loadResult')
			->willReturn(2);

		$this->assertSame([$return], (new StatsModel($mockDatabase))->getItems());
	}

	/**
	 * @testdox The model returns a single source's items from the database
	 *
	 * @covers  Stats\Models\StatsModel::getItems
	 */
	public function testTheModelReturnsASingleSourceItemsFromTheDatabase()
	{
		$return = [(object) ['php_version' => PHP_VERSION], (object) ['php_version' => PHP_VERSION]];

		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'getTableColumns', 'loadObjectList'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder(DatabaseQuery::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->once())
			->method('getQuery')
			->willReturn($mockQuery);

		$mockDatabase->expects($this->once())
			->method('getTableColumns')
			->willReturn(
				[
					'unique_id'   => 'varchar',
					'php_version' => 'varchar',
					'db_type'     => 'varchar',
					'db_version'  => 'varchar',
					'cms_version' => 'varchar',
					'server_os'   => 'varchar',
					'modified'    => 'datetime',
				]
			);

		$mockDatabase->expects($this->once())
			->method('loadObjectList')
			->willReturn($return);

		$this->assertSame($return, (new StatsModel($mockDatabase))->getItems('php_version'));
	}

	/**
	 * @testdox The model throws an Exception when an invalid source is specified
	 *
	 * @covers  Stats\Models\StatsModel::getItems
	 * @expectedException \InvalidArgumentException
	 */
	public function testTheModelThrowsAnExceptionWhenAnInvalidSourceIsSpecified()
	{
		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getTableColumns'])
			->getMockForAbstractClass();

		$mockDatabase->expects($this->once())
			->method('getTableColumns')
			->willReturn(
				[
					'unique_id'   => 'varchar',
					'php_version' => 'varchar',
					'db_type'     => 'varchar',
					'db_version'  => 'varchar',
					'cms_version' => 'varchar',
					'server_os'   => 'varchar',
					'modified'    => 'datetime',
				]
			);

		(new StatsModel($mockDatabase))->getItems('bad_column');
	}

	/**
	 * @testdox The model inserts a new record
	 *
	 * @covers  Stats\Models\StatsModel::save
	 */
	public function testTheModelInsertsANewRecord()
	{
		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'insertObject', 'loadResult', 'updateObject'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder(DatabaseQuery::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->once())
			->method('getQuery')
			->willReturn($mockQuery);

		$mockDatabase->expects($this->once())
			->method('insertObject');

		$mockDatabase->expects($this->once())
			->method('loadResult')
			->willReturn(0);

		$mockDatabase->expects($this->never())
			->method('updateObject');

		(new StatsModel($mockDatabase))->save((object) ['unique_id' => '1a', 'php_version' => PHP_VERSION]);
	}

	/**
	 * @testdox The model updates an existing record
	 *
	 * @covers  Stats\Models\StatsModel::save
	 */
	public function testTheModelUpdatesAnExistingRecord()
	{
		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'insertObject', 'loadResult', 'updateObject'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder(DatabaseQuery::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->once())
			->method('getQuery')
			->willReturn($mockQuery);

		$mockDatabase->expects($this->never())
			->method('insertObject');

		$mockDatabase->expects($this->once())
			->method('loadResult')
			->willReturn('1a');

		$mockDatabase->expects($this->once())
			->method('updateObject');

		(new StatsModel($mockDatabase))->save((object) ['unique_id' => '1a', 'php_version' => PHP_VERSION]);
	}
}
