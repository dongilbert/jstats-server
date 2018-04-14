<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\StatsServer\Router;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Router
 */
class RouterTest extends TestCase
{
	/**
	 * @testdox The router is instantiated correctly
	 *
	 * @covers  Joomla\StatsServer\Router::__construct
	 */
	public function testTheRouterIsInstantiatedCorrectly()
	{
		$mockInput = $this->getMockBuilder('Joomla\Input\Input')
			->disableOriginalConstructor()
			->getMock();

		$router = new Router($mockInput);

		$this->assertAttributeSame($mockInput, 'input', $router);
	}

	/**
	 * @testdox The router fetches the controller correctly
	 *
	 * @covers  Joomla\StatsServer\Router::fetchController
	 */
	public function testTheRouterFetchesTheControllerCorrectly()
	{
		$mockContainer = $this->getMockBuilder('Joomla\DI\Container')
			->getMock();

		$mockInput = $this->getMockBuilder('Joomla\Input\Input')
			->disableOriginalConstructor()
			->getMock();

		$mockInput->expects($this->exactly(2))
			->method('getMethod')
			->willReturn('GET');

		$controllerName = 'Joomla\StatsServer\Controllers\DisplayControllerGet';

		$mockController = $this->getMockBuilder($controllerName)
			->disableOriginalConstructor()
			->getMock();

		// Mock the response on buildSharedObject to return the mocked controller
		$mockContainer->expects($this->once())
			->method('get')
			->with($controllerName)
			->willReturn($mockController);

		$router = (new Router($mockInput))
			->setControllerPrefix('Joomla\StatsServer\\Controllers\\')
			->setDefaultController('DisplayController')
			->addMap('/submit', 'SubmitController')
			->addMap('/:source', 'DisplayController')
			->setContainer($mockContainer);

		$this->assertSame($mockController, $router->getController(''));
	}
}
