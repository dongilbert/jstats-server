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
	 * Data provider for testTheApplicationHandlesExceptionsCorrectly
	 *
	 * @return  array
	 */
	public function dataApplicationExceptions()
	{
		return [
			'401' => [401],
			'403' => [403],
			'404' => [404],
			'500' => [500],
		];
	}

	/**
	 * @testdox The application handles Exceptions correctly
	 *
	 * @param   integer  $code  The Exception code
	 *
	 * @covers  Stats\Application::doExecute
	 * @covers  Stats\Application::setErrorHeader
	 *
	 * @dataProvider dataApplicationExceptions
	 */
	public function testTheApplicationHandlesExceptionsCorrectly($code)
	{
		$mockController = $this->getMockBuilder('Stats\Controllers\DisplayControllerGet')
			->disableOriginalConstructor()
			->getMock();

		$mockController->expects($this->once())
			->method('execute')
			->willThrowException(new \Exception('Test failure', $code));

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

		// The status header should be first in the stack
		$statusHeader = $app->getHeaders()[0];

		$this->assertEquals($statusHeader['value'], $code, 'The Status header was not correctly set.');
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
