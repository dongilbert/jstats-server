<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Controllers;

use Joomla\Application\AbstractWebApplication;
use Joomla\Input\Input;
use Joomla\StatsServer\Controllers\SubmitControllerCreate;
use Joomla\StatsServer\Repositories\StatisticsRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Joomla\StatsServer\Controllers\SubmitControllerCreate
 */
class SubmitControllerCreateTest extends TestCase
{
	/**
	 * @testdox The controller is instantiated correctly
	 *
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::__construct
	 */
	public function testTheControllerIsInstantiatedCorrectly(): void
	{
		$this->markTestSkipped('Refactor as an integration test');

		$mockModel = $this->getMockBuilder(StatisticsRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = new SubmitControllerCreate($mockModel);

		$this->assertAttributeSame($mockModel, 'model', $controller);
	}

	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::execute
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkCMSVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkDatabaseType
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkPHPVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::validateVersionNumber
	 */
	public function testTheControllerIsExecutedCorrectly(): void
	{
		$this->markTestSkipped('Refactor as an integration test');

		$mockModel = $this->getMockBuilder(StatisticsRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('save');

		$mockApp = $this->getMockBuilder(AbstractWebApplication::class)
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
	 * @testdox The controller is executed correctly for a Joomla! 4.0 installation running PDO MySQL
	 *
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::execute
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkCMSVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkDatabaseType
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkPHPVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::validateVersionNumber
	 */
	public function testTheControllerIsExecutedCorrectlyForAJoomla4InstallationRunningPdoMysql(): void
	{
		$this->markTestSkipped('Refactor as an integration test');

		$mockModel = $this->getMockBuilder(StatisticsRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('save');

		$mockApp = $this->getMockBuilder(AbstractWebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$mockInput = $this->getMockBuilder(Input::class)
			->disableOriginalConstructor()
			->setMethods(['getRaw', 'getString'])
			->getMock();

		$mockInput->expects($this->exactly(3))
			->method('getRaw')
			->willReturnOnConsecutiveCalls(PHP_VERSION, '5.6.23', '4.0.0');

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
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::execute
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkCMSVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkDatabaseType
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkPHPVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::validateVersionNumber
	 */
	public function testTheControllerDoesNotAllowARecordWithNoCmsVersionToBeSaved(): void
	{
		$this->markTestSkipped('Refactor as an integration test');

		$mockModel = $this->getMockBuilder(StatisticsRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->never())
			->method('save');

		$mockLogger = $this->getMockBuilder(LoggerInterface::class)
			->getMock();

		$mockLogger->expects($this->once())
			->method('info');

		$mockApp = $this->getMockBuilder(AbstractWebApplication::class)
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
			->willReturnOnConsecutiveCalls(PHP_VERSION, '5.6.23', '');

		$mockInput->expects($this->exactly(3))
			->method('getString')
			->willReturnOnConsecutiveCalls('1a2b3c4d', 'mysql', 'Darwin 14.1.0');

		$controller = (new SubmitControllerCreate($mockModel))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}

	/**
	 * @testdox The controller does not allow a record with an incorrectly formatted CMS version number to be saved
	 *
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::execute
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkCMSVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkDatabaseType
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::checkPHPVersion
	 * @covers  Joomla\StatsServer\Controllers\SubmitControllerCreate::validateVersionNumber
	 */
	public function testTheControllerDoesNotAllowARecordWithAnIncorrectlyFormattedCmsVersionNumberToBeSaved(): void
	{
		$this->markTestSkipped('Refactor as an integration test');

		$mockModel = $this->getMockBuilder(StatisticsRepository::class)
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->never())
			->method('save');

		$mockLogger = $this->getMockBuilder(LoggerInterface::class)
			->getMock();

		$mockLogger->expects($this->once())
			->method('info');

		$mockApp = $this->getMockBuilder(AbstractWebApplication::class)
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
			->willReturnOnConsecutiveCalls(PHP_VERSION, '5.6.23', '3.5.0.1');

		$mockInput->expects($this->exactly(3))
			->method('getString')
			->willReturnOnConsecutiveCalls('1a2b3c4d', 'mysql', 'Darwin 14.1.0');

		$controller = (new SubmitControllerCreate($mockModel))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}
}
