<?php
namespace Stats\Tests\Providers;

use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Psr\Log\LoggerInterface;
use Stats\Database\Migrations;
use Stats\Providers\DatabaseServiceProvider;

/**
 * Test class for \Stats\Providers\DatabaseServiceProvider
 */
class DatabaseServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The database service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\DatabaseServiceProvider::register
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
	 * @covers  Stats\Providers\DatabaseServiceProvider::getDbService
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
	 * @covers  Stats\Providers\DatabaseServiceProvider::getDbMigrationsService
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
