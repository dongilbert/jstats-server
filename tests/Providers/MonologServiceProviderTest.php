<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Joomla\StatsServer\Providers\MonologServiceProvider;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Providers\MonologServiceProvider
 */
class MonologServiceProviderTest extends TestCase
{
	/**
	 * @testdox The Monolog service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::register
	 */
	public function testTheDatabaseServiceProviderIsRegisteredToTheContainer(): void
	{
		$container = new Container;
		$container->registerServiceProvider(new MonologServiceProvider);

		$this->assertTrue($container->exists('monolog.logger.database'));
	}

	/**
	 * @testdox The PSR-3 message processor service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologProcessorPsr3Service
	 */
	public function testThePsr3MessageProcessorServiceIsCreated(): void
	{
		$this->assertInstanceOf(
			PsrLogMessageProcessor::class, (new MonologServiceProvider)->getMonologProcessorPsr3Service($this->createMock(Container::class))
		);
	}

	/**
	 * @testdox The web message processor service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologProcessorWebService
	 */
	public function testTheWebMessageProcessorServiceIsCreated(): void
	{
		$this->assertInstanceOf(
			WebProcessor::class, (new MonologServiceProvider)->getMonologProcessorWebService($this->createMock(Container::class))
		);
	}

	/**
	 * @testdox The application message handler service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologHandlerApplicationService
	 */
	public function testTheApplicationMessageHandlerServiceIsCreated(): void
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
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologHandlerDatabaseService
	 */
	public function testTheDatabaseMessageHandlerServiceIsCreated(): void
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
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologLoggerApplicationService
	 */
	public function testTheApplicationLoggerServiceIsCreated(): void
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
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologLoggerCliService
	 */
	public function testTheCliLoggerServiceIsCreated(): void
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
	 * @covers  Joomla\StatsServer\Providers\MonologServiceProvider::getMonologLoggerDatabaseService
	 */
	public function testTheDatabaseLoggerServiceIsCreated(): void
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
