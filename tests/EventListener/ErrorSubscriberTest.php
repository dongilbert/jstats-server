<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\EventListener;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Event\ApplicationErrorEvent;
use Joomla\Console\Application as ConsoleApplication;
use Joomla\Console\Event\ApplicationErrorEvent as ConsoleApplicationErrorEvent;
use Joomla\Input\Input;
use Joomla\Router\Exception\MethodNotAllowedException;
use Joomla\Router\Exception\RouteNotFoundException;
use Joomla\StatsServer\EventListener\ErrorSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test class for \Joomla\StatsServer\EventListener\ErrorSubscriber
 */
class ErrorSubscriberTest extends TestCase
{
	/**
	 * @testdox Errors are handled for the console application
	 */
	public function testErrorsAreHandledForTheConsoleApplication()
	{
		$error = new \Exception('Testing');

		$output = new BufferedOutput;

		$application = $this->createMock(ConsoleApplication::class);

		$application->expects($this->once())
			->method('getConsoleInput')
			->willReturn($this->createMock(InputInterface::class));

		$application->expects($this->once())
			->method('getConsoleOutput')
			->willReturn($output);

		$event = new ConsoleApplicationErrorEvent($error, $application);

		$logger = new TestLogger;

		$subscriber = new ErrorSubscriber;
		$subscriber->setLogger($logger);
		$subscriber->handleConsoleError($event);

		$this->assertTrue(
			$logger->hasErrorThatContains('Uncaught Throwable of type Exception caught.'),
			'An error from the console application should be logged.'
		);

		$screenOutput = $output->fetch();

		$this->assertStringContainsString(
			'Uncaught Throwable of type Exception caught: Testing',
			$screenOutput,
			'An error from the console application should be output.'
		);
	}

	/**
	 * @testdox Method Not Allowed Errors are handled for the web application
	 */
	public function testMethodNotAllowedErrorsAreHandledForTheWebApplication()
	{
		$error = new MethodNotAllowedException(['POST']);

		$mockInput = new class extends Input
		{
			private $mockServer;

			public function __get($name)
			{
				if ($name === 'server')
				{
					return $this->getMockServer();
				}

				return parent::__get($name);
			}

			private function getMockServer(): Input
			{
				if ($this->mockServer === null)
				{
					$this->mockServer = new static(
						[
							'HTTP_HOST'       => 'developer.joomla.org',
							'HTTP_USER_AGENT' => 'JoomlaStatsTest/1.0',
							'REMOTE_ADDR'     => '127.0.0.1',
							'REQUEST_METHOD'  => 'GET',
						]
					);
				}

				return $this->mockServer;
			}
		};

		$application = $this->createMock(AbstractWebApplication::class);
		$application->input = $mockInput;

		$application->expects($this->once())
			->method('get')
			->with('uri.route')
			->willReturn('/hello');

		$application->expects($this->once())
			->method('allowCache')
			->with(false)
			->willReturn(false);

		$application->expects($this->once())
			->method('setResponse');

		$application->expects($this->once())
			->method('setHeader');

		$event = new ApplicationErrorEvent($error, $application);

		$logger = new TestLogger;

		$subscriber = new ErrorSubscriber;
		$subscriber->setLogger($logger);
		$subscriber->handleWebError($event);

		$this->assertTrue(
			$logger->hasErrorThatContains('Route `/hello` not supported by method `GET`'),
			'An error from the web application should be logged.'
		);
	}

	/**
	 * @testdox Route Not Found Errors are handled for the web application
	 */
	public function testRouteNotFoundErrorsAreHandledForTheWebApplication()
	{
		$error = new RouteNotFoundException('Testing', 404);

		$application = $this->createMock(AbstractWebApplication::class);

		$application->expects($this->once())
			->method('get')
			->with('uri.route')
			->willReturn('/hello');

		$application->expects($this->once())
			->method('allowCache')
			->with(false)
			->willReturn(false);

		$application->expects($this->once())
			->method('setResponse');

		$event = new ApplicationErrorEvent($error, $application);

		$logger = new TestLogger;

		$subscriber = new ErrorSubscriber;
		$subscriber->setLogger($logger);
		$subscriber->handleWebError($event);

		$this->assertTrue(
			$logger->hasErrorThatContains('Route `/hello` not found'),
			'An error from the web application should be logged.'
		);
	}

	/**
	 * @testdox Uncaught Exceptions are handled for the web application
	 */
	public function testUncaughtExceptionsAreHandledForTheWebApplication()
	{
		$error = new \Exception('Testing');

		$application = $this->createMock(AbstractWebApplication::class);

		$application->expects($this->once())
			->method('allowCache')
			->with(false)
			->willReturn(false);

		$application->expects($this->once())
			->method('setResponse');

		$event = new ApplicationErrorEvent($error, $application);

		$logger = new TestLogger;

		$subscriber = new ErrorSubscriber;
		$subscriber->setLogger($logger);
		$subscriber->handleWebError($event);

		$this->assertTrue(
			$logger->hasErrorThatContains('Uncaught Throwable of type Exception caught.'),
			'An error from the web application should be logged.'
		);
	}
}
