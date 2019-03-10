<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\Application\AbstractApplication;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Application\WebApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Router\Router;
use Joomla\StatsServer\Commands as AppCommands;
use Joomla\StatsServer\Console;
use Joomla\StatsServer\Controllers\DisplayControllerGet;
use Joomla\StatsServer\Database\Migrations;
use Joomla\StatsServer\GitHub\GitHub;
use Joomla\StatsServer\Models\StatsModel;
use Joomla\StatsServer\Providers\WebApplicationServiceProvider;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Test class for \Joomla\StatsServer\Providers\ApplicationServiceProvider
 */
class ApplicationServiceProviderTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->backupServer = $_SERVER;
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown(): void
	{
		$_SERVER = $this->backupServer;

		parent::tearDown();
	}

	/**
	 * @testdox The application service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::register
	 */
	public function testTheApplicationServiceProviderIsRegisteredToTheContainer(): void
	{
		$container = new Container;
		$container->registerServiceProvider(new WebApplicationServiceProvider);

		$this->assertTrue($container->exists(WebApplication::class));
	}

	/**
	 * @testdox The Analytics class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getAnalyticsService
	 */
	public function testTheAnalyticsClassServiceIsCreated(): void
	{
		$this->assertInstanceOf(Analytics::class, (new WebApplicationServiceProvider)->getAnalyticsService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The console service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getConsoleService
	 */
	public function testTheConsoleServiceIsCreated(): void
	{
		$this->assertInstanceOf(Console::class, (new WebApplicationServiceProvider)->getConsoleService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The Database\MigrateCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getDatabaseMigrateCommandService
	 */
	public function testTheDatabaseMigrateCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(Migrations::class)
			->willReturn($this->createMock(Migrations::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$mockContainer->expects($this->at(3))
			->method('get')
			->with(LoggerInterface::class)
			->willReturn($this->createMock(LoggerInterface::class));

		$this->assertInstanceOf(
			AppCommands\Database\MigrateCommand::class,
			(new WebApplicationServiceProvider)->getDatabaseMigrateCommandService($mockContainer)
		);
	}

	/**
	 * @testdox The Database\StatusCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getDatabaseStatusCommandService
	 */
	public function testTheDatabaseStatusCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(Migrations::class)
			->willReturn($this->createMock(Migrations::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(
			AppCommands\Database\StatusCommand::class,
			(new WebApplicationServiceProvider)->getDatabaseStatusCommandService($mockContainer)
		);
	}

	/**
	 * @testdox The DisplayControllerGet class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getDisplayControllerGetService
	 */
	public function testTheDisplayControllerGetClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(StatsJsonView::class)
			->willReturn($this->createMock(StatsJsonView::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(DisplayControllerGet::class, (new WebApplicationServiceProvider)->getDisplayControllerGetService($mockContainer));
	}

	/**
	 * @testdox The HelpCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getHelpCommandService
	 */
	public function testTheHelpCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(AppCommands\HelpCommand::class, (new WebApplicationServiceProvider)->getHelpCommandService($mockContainer));
	}

	/**
	 * @testdox The Input class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getInputService
	 */
	public function testTheInputClassServiceIsCreated(): void
	{
		$this->assertInstanceOf(Input::class, (new WebApplicationServiceProvider)->getInputService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The InstallCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getInstallCommandService
	 */
	public function testTheInstallCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(DatabaseDriver::class)
			->willReturn($this->createMock(DatabaseDriver::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(AppCommands\InstallCommand::class, (new WebApplicationServiceProvider)->getInstallCommandService($mockContainer));
	}

	/**
	 * @testdox The Router service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getRouterService
	 */
	public function testTheRouterServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);

		$this->assertInstanceOf(Router::class, (new WebApplicationServiceProvider)->getRouterService($mockContainer));
	}

	/**
	 * @testdox The SnapshotCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getSnapshotCommandService
	 */
	public function testTheSnapshotCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(StatsJsonView::class)
			->willReturn($this->createMock(StatsJsonView::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(AppCommands\SnapshotCommand::class, (new WebApplicationServiceProvider)->getSnapshotCommandService($mockContainer));
	}

	/**
	 * @testdox The StatsJsonView class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getStatsJsonViewService
	 */
	public function testTheStatsJsonViewClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(StatsModel::class)
			->willReturn($this->createMock(StatsModel::class));

		$this->assertInstanceOf(StatsJsonView::class, (new WebApplicationServiceProvider)->getStatsJsonViewService($mockContainer));
	}

	/**
	 * @testdox The StatsModel class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getStatsModelService
	 */
	public function testTheStatsModelClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(DatabaseDriver::class)
			->willReturn($this->createMock(DatabaseDriver::class));

		$this->assertInstanceOf(StatsModel::class, (new WebApplicationServiceProvider)->getStatsModelService($mockContainer));
	}

	/**
	 * @testdox The Tags\JoomlaCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getTagsJoomlaCommandService
	 */
	public function testTheTagsJoomlaCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(GitHub::class)
			->willReturn($this->createMock(GitHub::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(AppCommands\Tags\JoomlaCommand::class, (new WebApplicationServiceProvider)->getTagsJoomlaCommandService($mockContainer));
	}

	/**
	 * @testdox The Tags\PhpCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getTagsPhpCommandService
	 */
	public function testTheTagsPhpCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(GitHub::class)
			->willReturn($this->createMock(GitHub::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(AppCommands\Tags\PhpCommand::class, (new WebApplicationServiceProvider)->getTagsPhpCommandService($mockContainer));
	}

	/**
	 * @testdox The UpdateCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getUpdateCommandService
	 */
	public function testTheUpdateCommandClassServiceIsCreated(): void
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(AppCommands\UpdateCommand::class, (new WebApplicationServiceProvider)->getUpdateCommandService($mockContainer));
	}

	/**
	 * @testdox The web application service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getWebApplicationService
	 */
	public function testTheWebApplicationServiceIsCreated(): void
	{
		$mockInput = $this->getMockBuilder(Input::class)
			->setMethods(['get', 'getString'])
			->enableProxyingToOriginalMethods()
			->getMock();

		// Mock the Input object internals
		$mockServerInput = $this->getMockBuilder(Input::class)
			->setMethods(['get', 'set'])
			->setConstructorArgs([['HTTP_HOST' => 'mydomain.com']])
			->enableProxyingToOriginalMethods()
			->getMock();

		$property = (new \ReflectionClass($mockInput))->getProperty('inputs');
		$property->setAccessible(true);
		$property->setValue($mockInput, ['server' => $mockServerInput]);

		$mockContainer = $this->createMock(Container::class);

		$mockContainer->expects($this->at(0))
			->method('get')
			->with(ControllerResolverInterface::class)
			->willReturn($this->createMock(ControllerResolverInterface::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(Router::class)
			->willReturn($this->createMock(Router::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($mockInput);

		$mockContainer->expects($this->at(3))
			->method('get')
			->with('config')
			->willReturn($this->createMock(Registry::class));

		$mockContainer->expects($this->at(4))
			->method('get')
			->with(WebClient::class)
			->willReturn($this->createMock(WebClient::class));

		$mockContainer->expects($this->at(5))
			->method('get')
			->with(DispatcherInterface::class)
			->willReturn($this->createMock(DispatcherInterface::class));

		$mockContainer->expects($this->at(6))
			->method('get')
			->with(LoggerInterface::class)
			->willReturn($this->createMock(LoggerInterface::class));

		$this->assertInstanceOf(WebApplication::class, (new WebApplicationServiceProvider)->getWebApplicationService($mockContainer));
	}
}
