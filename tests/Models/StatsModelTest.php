<?php
namespace Stats\Tests\Models;

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

		$mockDatabase = $this->getMockBuilder('Joomla\Database\DatabaseDriver')
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'loadObjectList'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder('Joomla\Database\DatabaseQuery')
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$mockDatabase->expects($this->once())
			->method('getQuery')
			->willReturn($mockQuery);

		$mockDatabase->expects($this->once())
			->method('loadObjectList')
			->willReturn($return);

		$this->assertSame($return, (new StatsModel($mockDatabase))->getItems());
	}

	/**
	 * @testdox The model inserts a new record
	 *
	 * @covers  Stats\Models\StatsModel::save
	 */
	public function testTheModelInsertsANewRecord()
	{
		$mockDatabase = $this->getMockBuilder('Joomla\Database\DatabaseDriver')
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'insertObject', 'loadResult', 'updateObject'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder('Joomla\Database\DatabaseQuery')
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
		$mockDatabase = $this->getMockBuilder('Joomla\Database\DatabaseDriver')
			->disableOriginalConstructor()
			->setMethods(['getQuery', 'insertObject', 'loadResult', 'updateObject'])
			->getMockForAbstractClass();

		$mockQuery = $this->getMockBuilder('Joomla\Database\DatabaseQuery')
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
