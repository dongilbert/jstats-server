<?php
namespace Stats\Tests\Controllers;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Joomla\Input\Input;
use Stats\Application;
use Stats\Controllers\DisplayControllerGet;
use Stats\Views\Stats\StatsJsonView;

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
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockCache = $this->getMockBuilder(Cache::class)
			->getMock();

		$controller = new DisplayControllerGet($mockView, $mockCache);

		$this->assertAttributeSame($mockCache, 'cache', $controller);
		$this->assertAttributeSame($mockView, 'view', $controller);
	}

	/**
	 * @testdox The controller is executed correctly with no caching
	 *
	 * @covers  Stats\Controllers\DisplayControllerGet::execute
	 */
	public function testTheControllerIsExecutedCorrectlyWithNoCaching()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockView->expects($this->once())
			->method('render')
			->willReturn(json_encode(['error' => false]));

		$mockCache = $this->getMockBuilder(Cache::class)
			->getMock();

		$mockCache->expects($this->never())
			->method('contains');

		$mockApp = $this->getMockBuilder(Application::class)
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls('nope', false);

		$mockInput = $this->getMockBuilder(Input::class)
			->disableOriginalConstructor()
			->enableProxyingToOriginalMethods()
			->setMethods(['get'])
			->getMock();

		$mockInput->expects($this->once())
			->method('get')
			->willReturn(null);

		$controller = (new DisplayControllerGet($mockView, $mockCache))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}

	/**
	 * @testdox The controller is executed correctly with caching
	 *
	 * @covers  Stats\Controllers\DisplayControllerGet::execute
	 */
	public function testTheControllerIsExecutedCorrectlyWithCaching()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockView->expects($this->once())
			->method('render')
			->willReturn(json_encode(['error' => false]));

		$mockCache = new ArrayCache;

		$mockApp = $this->getMockBuilder(Application::class)
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->exactly(5))
			->method('get')
			->willReturnOnConsecutiveCalls('nope', true, 900, 'nope', true);

		$mockInput = $this->getMockBuilder(Input::class)
			->disableOriginalConstructor()
			->enableProxyingToOriginalMethods()
			->setMethods(['get'])
			->getMock();

		$mockInput->expects($this->exactly(2))
			->method('get')
			->willReturn(null);

		$controller = (new DisplayControllerGet($mockView, $mockCache))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());

		// Execute the controller a second time to validate the cache is used
		$controller->execute();

		$this->assertSame(1, $mockCache->getStats()['hits'], 'The request data should be served from the cache.');
	}
}
