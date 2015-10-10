<?php
namespace Stats\Tests;

use Stats\Router;

/**
 * Test class for \Stats\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The router is instantiated correctly
	 *
	 * @covers  Stats\Router::__construct
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
	 * @covers  Stats\Router::fetchController
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

		$controllerName = 'Stats\Controllers\DisplayControllerGet';

		$mockController = $this->getMockBuilder($controllerName)
			->disableOriginalConstructor()
			->getMock();

		// Mock the response on buildSharedObject to return the mocked controller
		$mockContainer->expects($this->once())
			->method('get')
			->with($controllerName)
			->willReturn($mockController);

		$router = (new Router($mockInput))
			->setControllerPrefix('Stats\\Controllers\\')
			->setDefaultController('DisplayController')
			->addMap('/submit', 'SubmitController')
			->addMap('/:source', 'DisplayController')
			->setContainer($mockContainer);

		$this->assertSame($mockController, $router->getController(''));
	}
}
