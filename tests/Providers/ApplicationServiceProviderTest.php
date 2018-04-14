<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\Application\AbstractApplication;
use Joomla\Application\Cli\{
	CliInput, CliOutput, Output\Processor\ColorProcessor
};
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\Input\{
	Cli, Input
};
use Joomla\Registry\Registry;
use Joomla\Test\TestHelper;
use Joomla\StatsServer\{
	CliApplication, Console, Router, WebApplication
};
use Joomla\StatsServer\Commands as AppCommands;
use Joomla\StatsServer\Controllers\{
	DisplayControllerGet, SubmitControllerCreate, SubmitControllerGet
};
use Joomla\StatsServer\Database\Migrations;
use Joomla\StatsServer\GitHub\GitHub;
use Joomla\StatsServer\Models\StatsModel;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use Joomla\StatsServer\Providers\ApplicationServiceProvider;
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
	public function setUp()
	{
		parent::setUp();

		$this->backupServer = $_SERVER;
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		parent::tearDown();
	}

	/**
	 * @testdox The application service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::register
	 */
	public function testTheApplicationServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new ApplicationServiceProvider);

		$this->assertTrue($container->exists(WebApplication::class));
	}

	/**
	 * @testdox The Analytics class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getAnalyticsService
	 */
	public function testTheAnalyticsClassServiceIsCreated()
	{
		$this->assertInstanceOf(Analytics::class, (new ApplicationServiceProvider)->getAnalyticsService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The CLI application service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getCliApplicationService
	 */
	public function testTheCliApplicationServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(Cli::class)
			->willReturn($this->createMock(Cli::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with('config')
			->willReturn($this->createMock(Registry::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(CliOutput::class)
			->willReturn($this->createMock(CliOutput::class));

		$mockContainer->expects($this->at(3))
			->method('get')
			->with(CliInput::class)
			->willReturn($this->createMock(CliInput::class));

		$mockContainer->expects($this->at(4))
			->method('get')
			->with(Console::class)
			->willReturn($this->createMock(Console::class));

		$mockContainer->expects($this->at(5))
			->method('get')
			->with('monolog.logger.cli')
			->willReturn($this->createMock(LoggerInterface::class));

		$this->assertInstanceOf(CliApplication::class, (new ApplicationServiceProvider)->getCliApplicationService($mockContainer));
	}

	/**
	 * @testdox The CliInput class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getCliInputService
	 */
	public function testTheCliInputClassServiceIsCreated()
	{
		$this->assertInstanceOf(CliInput::class, (new ApplicationServiceProvider)->getCliInputService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The CliOutput class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getCliOutputService
	 */
	public function testTheCliOutputClassServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->any())
			->method('get')
			->with(ColorProcessor::class)
			->willReturn($this->createMock(ColorProcessor::class));

		$this->assertInstanceOf(CliOutput::class, (new ApplicationServiceProvider)->getCliOutputService($mockContainer));
	}

	/**
	 * @testdox The ColorProcessor class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getColorProcessorService
	 */
	public function testTheColorProcessorClassServiceIsCreated()
	{
		$mockInput = $this->getMockBuilder(Cli::class)
			->setMethods(['get', 'getBool'])
			->enableProxyingToOriginalMethods()
			->getMock();

		$mockInput->expects($this->once())
			->method('getBool')
			->willReturn(false);

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->any())
			->method('get')
			->with(Cli::class)
			->willReturn($mockInput);

		$this->assertInstanceOf(ColorProcessor::class, (new ApplicationServiceProvider)->getColorProcessorService($mockContainer));
	}

	/**
	 * @testdox The console service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getConsoleService
	 */
	public function testTheConsoleServiceIsCreated()
	{
		$this->assertInstanceOf(Console::class, (new ApplicationServiceProvider)->getConsoleService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The Database\MigrateCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getDatabaseMigrateCommandService
	 */
	public function testTheDatabaseMigrateCommandClassServiceIsCreated()
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
			(new ApplicationServiceProvider)->getDatabaseMigrateCommandService($mockContainer)
		);
	}

	/**
	 * @testdox The Database\StatusCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getDatabaseStatusCommandService
	 */
	public function testTheDatabaseStatusCommandClassServiceIsCreated()
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
			(new ApplicationServiceProvider)->getDatabaseStatusCommandService($mockContainer)
		);
	}

	/**
	 * @testdox The DisplayControllerGet class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getDisplayControllerGetService
	 */
	public function testTheDisplayControllerGetClassServiceIsCreated()
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

		$this->assertInstanceOf(DisplayControllerGet::class, (new ApplicationServiceProvider)->getDisplayControllerGetService($mockContainer));
	}

	/**
	 * @testdox The HelpCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getHelpCommandService
	 */
	public function testTheHelpCommandClassServiceIsCreated()
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

		$this->assertInstanceOf(AppCommands\HelpCommand::class, (new ApplicationServiceProvider)->getHelpCommandService($mockContainer));
	}

	/**
	 * @testdox The Input class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getInputService
	 */
	public function testTheInputClassServiceIsCreated()
	{
		$this->assertInstanceOf(Input::class, (new ApplicationServiceProvider)->getInputService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The Input\Cli class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getInputCliService
	 */
	public function testTheInputCliClassServiceIsCreated()
	{
		$this->assertInstanceOf(Cli::class, (new ApplicationServiceProvider)->getInputCliService($this->createMock(Container::class)));
	}

	/**
	 * @testdox The InstallCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getInstallCommandService
	 */
	public function testTheInstallCommandClassServiceIsCreated()
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

		$this->assertInstanceOf(AppCommands\InstallCommand::class, (new ApplicationServiceProvider)->getInstallCommandService($mockContainer));
	}

	/**
	 * @testdox The Router service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getRouterService
	 */
	public function testTheRouterServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->any())
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(Router::class, (new ApplicationServiceProvider)->getRouterService($mockContainer));
	}

	/**
	 * @testdox The SnapshotCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getSnapshotCommandService
	 */
	public function testTheSnapshotCommandClassServiceIsCreated()
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

		$this->assertInstanceOf(AppCommands\SnapshotCommand::class, (new ApplicationServiceProvider)->getSnapshotCommandService($mockContainer));
	}

	/**
	 * @testdox The SubmitControllerCreate class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getSubmitControllerCreateService
	 */
	public function testTheSubmitControllerCreateClassServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(StatsModel::class)
			->willReturn($this->createMock(StatsModel::class));

		$mockContainer->expects($this->at(1))
			->method('get')
			->with(AbstractApplication::class)
			->willReturn($this->createMock(AbstractApplication::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Input::class)
			->willReturn($this->createMock(Input::class));

		$this->assertInstanceOf(SubmitControllerCreate::class, (new ApplicationServiceProvider)->getSubmitControllerCreateService($mockContainer));
	}

	/**
	 * @testdox The StatsJsonView class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getStatsJsonViewService
	 */
	public function testTheStatsJsonViewClassServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(StatsModel::class)
			->willReturn($this->createMock(StatsModel::class));

		$this->assertInstanceOf(StatsJsonView::class, (new ApplicationServiceProvider)->getStatsJsonViewService($mockContainer));
	}

	/**
	 * @testdox The StatsModel class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getStatsModelService
	 */
	public function testTheStatsModelClassServiceIsCreated()
	{
		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->at(0))
			->method('get')
			->with(DatabaseDriver::class)
			->willReturn($this->createMock(DatabaseDriver::class));

		$this->assertInstanceOf(StatsModel::class, (new ApplicationServiceProvider)->getStatsModelService($mockContainer));
	}

	/**
	 * @testdox The SubmitControllerGet class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getSubmitControllerGetService
	 */
	public function testTheSubmitControllerGetClassServiceIsCreated()
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

		$this->assertInstanceOf(SubmitControllerGet::class, (new ApplicationServiceProvider)->getSubmitControllerGetService($mockContainer));
	}

	/**
	 * @testdox The Tags\JoomlaCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getTagsJoomlaCommandService
	 */
	public function testTheTagsJoomlaCommandClassServiceIsCreated()
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

		$this->assertInstanceOf(AppCommands\Tags\JoomlaCommand::class, (new ApplicationServiceProvider)->getTagsJoomlaCommandService($mockContainer));
	}

	/**
	 * @testdox The Tags\PhpCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getTagsPhpCommandService
	 */
	public function testTheTagsPhpCommandClassServiceIsCreated()
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

		$this->assertInstanceOf(AppCommands\Tags\PhpCommand::class, (new ApplicationServiceProvider)->getTagsPhpCommandService($mockContainer));
	}

	/**
	 * @testdox The UpdateCommand class service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getUpdateCommandService
	 */
	public function testTheUpdateCommandClassServiceIsCreated()
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

		$this->assertInstanceOf(AppCommands\UpdateCommand::class, (new ApplicationServiceProvider)->getUpdateCommandService($mockContainer));
	}

	/**
	 * @testdox The web application service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ApplicationServiceProvider::getWebApplicationService
	 */
	public function testTheWebApplicationServiceIsCreated()
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

		TestHelper::setValue($mockInput, 'inputs', ['server' => $mockServerInput]);

		$mockContainer = $this->createMock(Container::class);

		$mockContainer->expects($this->at(0))
			->method('get')
			->with(Input::class)
			->willReturn($mockInput);

		$mockContainer->expects($this->at(1))
			->method('get')
			->with('config')
			->willReturn($this->createMock(Registry::class));

		$mockContainer->expects($this->at(2))
			->method('get')
			->with(Analytics::class)
			->willReturn($this->createMock(Analytics::class));

		$mockContainer->expects($this->at(3))
			->method('get')
			->with('monolog.logger.application')
			->willReturn($this->createMock(LoggerInterface::class));

		$mockContainer->expects($this->at(4))
			->method('get')
			->with(Router::class)
			->willReturn($this->createMock(Router::class));

		$this->assertInstanceOf(WebApplication::class, (new ApplicationServiceProvider)->getWebApplicationService($mockContainer));
	}
}
