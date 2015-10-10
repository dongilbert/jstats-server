<?php
namespace Stats\Tests\Controllers;

use Stats\Controllers\DisplayControllerGet;

/**
 * Test class for \Stats\Controllers\DisplayControllerGet
 */
class DisplayControllerGetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The controller is instantiated correctly
	 *
	 * @covers  Stats\Controllers\DisplayControllerGet::__construct
	 */
	public function testTheControllerIsInstantiatedCorrectly()
	{
		$mockView = $this->getMockBuilder('Stats\Views\Stats\StatsJsonView')
			->disableOriginalConstructor()
			->getMock();

		$controller = new DisplayControllerGet($mockView);

		$this->assertAttributeSame($mockView, 'view', $controller);
	}

	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Stats\Controllers\DisplayControllerGet::execute
	 */
	public function testTheControllerIsExecutedCorrectly()
	{
		$mockView = $this->getMockBuilder('Stats\Views\Stats\StatsJsonView')
			->disableOriginalConstructor()
			->getMock();

		$mockView->expects($this->once())
			->method('render')
			->willReturn(json_encode(['error' => false]));

		$mockApp = $this->getMockBuilder('Stats\Application')
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->once())
			->method('get')
			->willReturn('nope');

		$mockInput = $this->getMockBuilder('Joomla\Input\Input')
			->disableOriginalConstructor()
			->enableProxyingToOriginalMethods()
			->setMethods(['get'])
			->getMock();

		$mockInput->expects($this->once())
			->method('get')
			->willReturn(null);

		$controller = (new DisplayControllerGet($mockView))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}
}
