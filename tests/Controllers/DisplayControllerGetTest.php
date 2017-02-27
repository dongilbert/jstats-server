<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Controllers;

use Joomla\Cache\Adapter\Runtime;
use Joomla\Input\Input;
use Joomla\StatsServer\Controllers\DisplayControllerGet;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use Joomla\StatsServer\WebApplication;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Test class for \Joomla\StatsServer\Controllers\DisplayControllerGet
 */
class DisplayControllerGetTest extends TestCase
{
	/**
	 * @testdox The controller is instantiated correctly
	 *
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerGet::__construct
	 */
	public function testTheControllerIsInstantiatedCorrectly()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockCache = $this->getMockBuilder(CacheItemPoolInterface::class)
			->getMock();

		$controller = new DisplayControllerGet($mockView, $mockCache);

		$this->assertAttributeSame($mockCache, 'cache', $controller);
		$this->assertAttributeSame($mockView, 'view', $controller);
	}

	/**
	 * @testdox The controller is executed correctly with no caching
	 *
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerGet::execute
	 */
	public function testTheControllerIsExecutedCorrectlyWithNoCaching()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockView->expects($this->once())
			->method('render')
			->willReturn(json_encode(['error' => false]));

		$mockCache = $this->getMockBuilder(CacheItemPoolInterface::class)
			->getMock();

		$mockCache->expects($this->never())
			->method('hasItem');

		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls('nope', false);

		$mockInput = $this->getMockBuilder(Input::class)
			->setConstructorArgs([[]])
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
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerGet::cacheData
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerGet::execute
	 */
	public function testTheControllerIsExecutedCorrectlyWithCaching()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockView->expects($this->once())
			->method('render')
			->willReturn(json_encode(['error' => false]));

		$mockCache = new Runtime;

		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->exactly(5))
			->method('get')
			->willReturnOnConsecutiveCalls('nope', true, 900, 'nope', true);

		$mockInput = $this->getMockBuilder(Input::class)
			->setConstructorArgs([[]])
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

		$this->assertAttributeNotEmpty('db', $mockCache, 'The request data for the second controller execution should be served from the cache.');
	}
}
