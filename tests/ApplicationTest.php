<?php
namespace Stats\Tests;

use Stats\Application;

/**
 * Test class for \Stats\Application
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The application executes correctly
	 *
	 * @covers  Stats\Application::doExecute
	 */
	public function testTheApplicationExecutesCorrectly()
	{
		$mockController = $this->getMockBuilder('Stats\Controllers\DisplayControllerGet')
			->disableOriginalConstructor()
			->getMock();

		$mockController->expects($this->once())
			->method('execute')
			->willReturn(true);

		$mockRouter = $this->getMockBuilder('Stats\Router')
			->disableOriginalConstructor()
			->getMock();

		$mockRouter->expects($this->once())
			->method('getController')
			->willReturn($mockController);

		(new Application)
			->setRouter($mockRouter)
			->execute();
	}

	/**
	 * @testdox The application handles Exceptions correctly
	 *
	 * @covers  Stats\Application::doExecute
	 * @covers  Stats\Application::setErrorHeader
	 */
	public function testTheApplicationHandlesExceptionsCorrectly()
	{
		$mockController = $this->getMockBuilder('Stats\Controllers\DisplayControllerGet')
			->disableOriginalConstructor()
			->getMock();

		$mockController->expects($this->once())
			->method('execute')
			->willThrowException(new \Exception('Test failure', 404));

		$mockRouter = $this->getMockBuilder('Stats\Router')
			->disableOriginalConstructor()
			->getMock();

		$mockRouter->expects($this->once())
			->method('getController')
			->willReturn($mockController);

		$app = new Application;
		$app->setRouter($mockRouter);

		// The execute method sends the response, which includes the body output; catch it in a buffer
		ob_start();
		$app->execute();
		ob_end_clean();
	}

	/**
	 * @testdox The router is set to the application
	 *
	 * @covers  Stats\Application::setRouter
	 */
	public function testTheRouterIsSetToTheApplication()
	{
		$mockRouter = $this->getMockBuilder('Stats\Router')
			->disableOriginalConstructor()
			->getMock();

		$app = new Application;
		$app->setRouter($mockRouter);

		$this->assertAttributeSame($mockRouter, 'router', $app);
	}
}
