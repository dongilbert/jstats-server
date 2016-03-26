<?php

namespace Stats\Tests\Controllers;

use Joomla\Input\Input;
use Psr\Log\LoggerInterface;
use Stats\Controllers\SubmitControllerCreate;
use Stats\Models\StatsModel;
use Stats\WebApplication;

/**
 * Test class for \Stats\Controllers\SubmitControllerCreate
 */
class SubmitControllerCreateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The controller is instantiated correctly
	 *
	 * @covers  Stats\Controllers\SubmitControllerCreate::__construct
	 */
	public function testTheControllerIsInstantiatedCorrectly()
	{
		$mockModel = $this->getMockBuilder(StatsModel::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = new SubmitControllerCreate($mockModel);

		$this->assertAttributeSame($mockModel, 'model', $controller);
	}

	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Stats\Controllers\SubmitControllerCreate::execute
	 * @covers  Stats\Controllers\SubmitControllerCreate::checkCMSVersion
	 * @covers  Stats\Controllers\SubmitControllerCreate::checkDatabaseType
	 * @covers  Stats\Controllers\SubmitControllerCreate::checkPHPVersion
	 * @covers  Stats\Controllers\SubmitControllerCreate::validateVersionNumber
	 */
	public function testTheControllerIsExecutedCorrectly()
	{
		$mockModel = $this->getMockBuilder(StatsModel::class)
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('save');

		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$mockInput = $this->getMockBuilder(Input::class)
			->disableOriginalConstructor()
			->setMethods(['getRaw', 'getString'])
			->getMock();

		$mockInput->expects($this->exactly(3))
			->method('getRaw')
			->willReturnOnConsecutiveCalls(PHP_VERSION, '5.6.23', '3.5.0');

		$mockInput->expects($this->exactly(3))
			->method('getString')
			->willReturnOnConsecutiveCalls('1a2b3c4d', 'mysql', 'Darwin 14.1.0');

		$controller = (new SubmitControllerCreate($mockModel))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}

	/**
	 * @testdox The controller does not allow a record with no CMS version to be saved
	 *
	 * @covers  Stats\Controllers\SubmitControllerCreate::execute
	 * @covers  Stats\Controllers\SubmitControllerCreate::checkCMSVersion
	 * @covers  Stats\Controllers\SubmitControllerCreate::checkDatabaseType
	 * @covers  Stats\Controllers\SubmitControllerCreate::checkPHPVersion
	 * @covers  Stats\Controllers\SubmitControllerCreate::validateVersionNumber
	 */
	public function testTheControllerDoesNotAllowARecordWithNoCmsVersionToBeSaved()
	{
		$mockModel = $this->getMockBuilder(StatsModel::class)
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->never())
			->method('save');

		$mockLogger = $this->getMockBuilder(LoggerInterface::class)
			->getMock();

		$mockLogger->expects($this->once())
			->method('info');

		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->once())
			->method('getLogger')
			->willReturn($mockLogger);

		$mockInput = $this->getMockBuilder(Input::class)
			->disableOriginalConstructor()
			->setMethods(['getRaw', 'getString'])
			->getMock();

		$mockInput->expects($this->exactly(3))
			->method('getRaw')
			->willReturnOnConsecutiveCalls(PHP_VERSION, '5.6.23', null);

		$mockInput->expects($this->exactly(3))
			->method('getString')
			->willReturnOnConsecutiveCalls('1a2b3c4d', 'mysql', 'Darwin 14.1.0');

		$controller = (new SubmitControllerCreate($mockModel))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}
}
