<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Models;

use Joomla\Database\{
	DatabaseDriver, DatabaseQuery
};
use Joomla\StatsServer\Models\StatsModel;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Models\StatsModel
 */
class StatsModelTest extends TestCase
{
	/**
	 * @testdox The model returns all items from the database
	 *
	 * @covers  Joomla\StatsServer\Models\StatsModel::getItems
	 */
	public function testTheModelReturnsAllItemsFromTheDatabase()
	{
		$return = [['unique_id' => '1a'], ['unique_id' => '2b']];

		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'loadAssocList', 'loadResult'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder(DatabaseQuery::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->exactly(2))
			->method('getQuery')
			->willReturn($mockQuery);

		$mockDatabase->expects($this->once())
			->method('loadAssocList')
			->willReturn($return);

		$mockDatabase->expects($this->once())
			->method('loadResult')
			->willReturn(2);

		$items = (new StatsModel($mockDatabase))->getItems();

		$this->assertInstanceOf('Generator', $items);

		foreach ($items as $value)
		{
			$data = $value;
		}

		$this->assertSame($data, $return);
	}

	/**
	 * @testdox The model returns a single source's items from the database
	 *
	 * @covers  Joomla\StatsServer\Models\StatsModel::getItems
	 */
	public function testTheModelReturnsASingleSourceItemsFromTheDatabase()
	{
		$return = [['php_version' => PHP_VERSION], ['php_version' => PHP_VERSION]];

		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'getTableColumns', 'loadAssocList', 'loadResult'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder(DatabaseQuery::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->exactly(2))
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
			->method('loadAssocList')
			->willReturn($return);

		$mockDatabase->expects($this->once())
			->method('loadResult')
			->willReturn(2);

		$items = (new StatsModel($mockDatabase))->getItems('php_version');

		$this->assertInstanceOf('Generator', $items);

		foreach ($items as $value)
		{
			$data = $value;
		}

		$this->assertSame($data, $return);
	}

	/**
	 * @testdox The model throws an Exception when an invalid source is specified
	 *
	 * @covers  Joomla\StatsServer\Models\StatsModel::getItems
	 * @expectedException \InvalidArgumentException
	 */
	public function testTheModelThrowsAnExceptionWhenAnInvalidSourceIsSpecified()
	{
		$mockDatabase = $this->getMockBuilder(DatabaseDriver::class)
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'getTableColumns', 'loadResult'])
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
			->method('loadResult')
			->willReturn(2);

		$items = (new StatsModel($mockDatabase))->getItems('bad_column');

		$this->assertInstanceOf('Generator', $items);

		foreach ($items as $value)
		{
			$data = $value;
		}
	}

	/**
	 * @testdox The model inserts a new record
	 *
	 * @covers  Joomla\StatsServer\Models\StatsModel::save
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
	 * @covers  Joomla\StatsServer\Models\StatsModel::save
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
