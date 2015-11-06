<?php
namespace Stats\Tests\Controllers;

use Stats\Controllers\SubmitControllerCreate;

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
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$controller = new SubmitControllerCreate($mockModel);

		$this->assertAttributeSame($mockModel, 'model', $controller);
	}

	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Stats\Controllers\SubmitControllerCreate::execute
	 */
	public function testTheControllerIsExecutedCorrectly()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('save');

		$mockApp = $this->getMockBuilder('Stats\Application')
			->disableOriginalConstructor()
			->getMock();

		$mockInput = $this->getMockBuilder('Joomla\Input\Input')
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
	 * @expectedException  \RuntimeException
	 */
	public function testTheControllerDoesNotAllowARecordWithNoCmsVersionToBeSaved()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->never())
			->method('save');

		$mockLogger = $this->getMockBuilder('Psr\Log\LoggerInterface')
			->getMock();

		$mockLogger->expects($this->once())
			->method('info');

		$mockApp = $this->getMockBuilder('Stats\Application')
			->disableOriginalConstructor()
			->setMethods(['getLogger'])
			->getMock();

		$mockApp->expects($this->once())
			->method('getLogger')
			->willReturn($mockLogger);

		$mockInput = $this->getMockBuilder('Joomla\Input\Input')
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

		$controller->execute();
	}
}
