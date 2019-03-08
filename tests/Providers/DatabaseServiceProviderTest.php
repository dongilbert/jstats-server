<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Monitor\LoggingMonitor;
use Joomla\Database\Service\DatabaseProvider;
use Joomla\DI\Container;
use Joomla\StatsServer\Database\Migrations;
use Joomla\StatsServer\Providers\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Providers\DatabaseServiceProvider
 */
class DatabaseServiceProviderTest extends TestCase
{
	/**
	 * @testdox The database service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\DatabaseServiceProvider::register
	 */
	public function testTheDatabaseServiceProviderIsRegisteredToTheContainer(): void
	{
		$container = new Container;
		$container->registerServiceProvider(new DatabaseProvider);
		$container->registerServiceProvider(new DatabaseServiceProvider);

		$this->assertTrue($container->exists(LoggingMonitor::class));
	}

	/**
	 * @testdox The database driver service is extended
	 *
	 * @covers  Joomla\StatsServer\Providers\DatabaseServiceProvider::extendDatabaseDriverService
	 */
	public function testTheDatabaseDriverServiceIsExtended(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->willReturn($this->createMock(LoggingMonitor::class));

		$mockDatabase = $this->createMock(DatabaseDriver::class);
		$mockDatabase->expects($this->once())
			->method('setMonitor');

		$this->assertInstanceOf(
			DatabaseDriver::class,
			(new DatabaseServiceProvider)->extendDatabaseDriverService($mockDatabase, $mockContainer)
		);
	}

	/**
	 * @testdox The database migrations service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\DatabaseServiceProvider::getDbMigrationsService
	 */
	public function testTheDatabaseMigrationsServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with(DatabaseDriver::class)
			->willReturn($this->createMock(DatabaseDriver::class));

		$this->assertInstanceOf(Migrations::class, (new DatabaseServiceProvider)->getDbMigrationsService($mockContainer));
	}
}
