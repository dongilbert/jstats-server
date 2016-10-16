<?php

namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;
use Stats\Providers\MonologServiceProvider;

/**
 * Test class for \Stats\Providers\MonologServiceProvider
 */
class MonologServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The Monolog service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::register
	 */
	public function testTheDatabaseServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new MonologServiceProvider);

		$this->assertTrue($container->exists('monolog.logger.database'));
	}

	/**
	 * @testdox The PSR-3 message processor service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologProcessorPsr3Service
	 */
	public function testThePsr3MessageProcessorServiceIsCreated()
	{
		$this->assertInstanceOf(
			PsrLogMessageProcessor::class, (new MonologServiceProvider)->getMonologProcessorPsr3Service($this->createMock(Container::class))
		);
	}

	/**
	 * @testdox The web message processor service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologProcessorWebService
	 */
	public function testTheWebMessageProcessorServiceIsCreated()
	{
		$this->assertInstanceOf(
			WebProcessor::class, (new MonologServiceProvider)->getMonologProcessorWebService($this->createMock(Container::class))
		);
	}

	/**
	 * @testdox The application message handler service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologHandlerApplicationService
	 */
	public function testTheApplicationMessageHandlerServiceIsCreated()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(2))
			->method('get')
			->willReturn(null, 'error');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(
			StreamHandler::class,
			(new MonologServiceProvider)->getMonologHandlerApplicationService($mockContainer)
		);
	}

	/**
	 * @testdox The database message handler service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologHandlerDatabaseService
	 */
	public function testTheDatabaseMessageHandlerServiceIsCreated()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(3))
			->method('get')
			->willReturnOnConsecutiveCalls(null, null, 'error');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(
			StreamHandler::class,
			(new MonologServiceProvider)->getMonologHandlerDatabaseService($mockContainer)
		);
	}

	/**
	 * @testdox The application logger service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologLoggerApplicationService
	 */
	public function testTheApplicationLoggerServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls($this->createMock(HandlerInterface::class), $this->createMock(WebProcessor::class));

		$this->assertInstanceOf(
			Logger::class,
			(new MonologServiceProvider)->getMonologLoggerApplicationService($mockContainer)
		);
	}

	/**
	 * @testdox The CLI logger service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologLoggerCliService
	 */
	public function testTheCliLoggerServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('monolog.handler.application')
			->willReturn($this->createMock(HandlerInterface::class));

		$this->assertInstanceOf(
			Logger::class,
			(new MonologServiceProvider)->getMonologLoggerCliService($mockContainer)
		);
	}

	/**
	 * @testdox The database logger service is created
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::getMonologLoggerDatabaseService
	 */
	public function testTheDatabaseLoggerServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->exactly(3))
			->method('get')
			->willReturnOnConsecutiveCalls(
				$this->createMock(HandlerInterface::class),
				$this->createMock(PsrLogMessageProcessor::class),
				$this->createMock(WebProcessor::class)
			);

		$this->assertInstanceOf(
			Logger::class,
			(new MonologServiceProvider)->getMonologLoggerDatabaseService($mockContainer)
		);
	}
}
