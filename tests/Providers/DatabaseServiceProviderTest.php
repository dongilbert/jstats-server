<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Joomla\StatsServer\Database\Migrations;
use Joomla\StatsServer\Providers\DatabaseServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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
	public function testTheDatabaseServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new DatabaseServiceProvider);

		$this->assertTrue($container->exists(DatabaseDriver::class));
	}

	/**
	 * @testdox The database driver service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\DatabaseServiceProvider::getDbService
	 */
	public function testTheDatabaseDriverServiceIsCreated()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls(['driver' => 'mysqli'], false);

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls($mockConfig, $this->createMock(LoggerInterface::class));

		$this->assertInstanceOf(DatabaseDriver::class, (new DatabaseServiceProvider)->getDbService($mockContainer));
	}

	/**
	 * @testdox The database migrations service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\DatabaseServiceProvider::getDbMigrationsService
	 */
	public function testTheDatabaseMigrationsServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('db')
			->willReturn($this->createMock(DatabaseDriver::class));

		$this->assertInstanceOf(Migrations::class, (new DatabaseServiceProvider)->getDbMigrationsService($mockContainer));
	}
}
