<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Controllers;

use Joomla\Application\AbstractApplication;
use Joomla\Application\WebApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\StatsServer\Controllers\SubmitDataController;
use Joomla\StatsServer\Kernel\WebKernel;
use Joomla\StatsServer\Tests\DatabaseTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Test class for \Joomla\StatsServer\Controllers\SubmitDataController
 */
class SubmitDataControllerTest extends DatabaseTestCase
{
	/**
	 * Application kernel to use with testing
	 *
	 * @var  WebKernel
	 */
	private $kernel;

	/**
	 * Application logger
	 *
	 * @var  TestLogger
	 */
	private $logger;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		static::$dbManager->runMigrations();
	}

	/**
	 * This method is called before each test.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->logger = new TestLogger;

		$this->kernel = new class(static::$connection, $this->logger) extends WebKernel
		{
			private $database;
			private $logger;

			public function __construct(DatabaseInterface $database, LoggerInterface $logger)
			{
				$this->database = $database;
				$this->logger   = $logger;
			}

			protected function buildContainer(): Container
			{
				$container = parent::buildContainer();

				// Overload the database and logger services with the test environment resources
				$container->share(DatabaseDriver::class, $this->database);
				$container->share(LoggerInterface::class, $this->logger);

				return $container;
			}

			public function getContainer()
			{
				return parent::getContainer();
			}
		};
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown(): void
	{
		static::$dbManager->clearTables();

		parent::tearDown();
	}

	/**
	 * @testdox Statistics are submitted
	 */
	public function testStatisticsAreSubmitted(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data
		$application->input->set('php_version', '5.5.38');
		$application->input->set('db_version', '5.6.41');
		$application->input->set('cms_version', '3.9.0');
		$application->input->set('unique_id', 'a1b2c3d4');
		$application->input->set('db_type', 'mysqli');
		$application->input->set('server_os', 'Darwin 14.1.0');

		/** @var SubmitDataController $controller */
		$controller = $this->kernel->getContainer()->get(SubmitDataController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(200, $response->getStatusCode());

		$this->assertSame(
			[
				'error'   => false,
				'message' => 'Data saved successfully',
			],
			$response->getPayload()
		);
	}

	/**
	 * @testdox Statistics are sanitized
	 */
	public function testStatisticsAreSanitized(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data
		$application->input->set('php_version', '5.5.9-1ubuntu4.11');
		$application->input->set('db_version', '5.7.14-google-log');
		$application->input->set('cms_version', '3.9.0');
		$application->input->set('unique_id', 'a1b2c3d4');
		$application->input->set('db_type', 'mysqli');
		$application->input->set('server_os', 'Darwin 14.1.0');

		/** @var SubmitDataController $controller */
		$controller = $this->kernel->getContainer()->get(SubmitDataController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(200, $response->getStatusCode());

		$this->assertSame(
			[
				'error'   => false,
				'message' => 'Data saved successfully',
			],
			$response->getPayload()
		);

		// Get record from database and validate it was saved with sanitized versions
		$record = static::$connection->setQuery(
			static::$connection->getQuery(true)
				->select('*')
				->from('#__jstats')
				->where('unique_id = ' . static::$connection->quote('a1b2c3d4'))
		)->loadObject();

		if (!isset($record->unique_id))
		{
			$this->fail('Record was not queried from the database correctly.');
		}

		$this->assertSame('5.7.14', $record->db_version);
		$this->assertSame('5.5.9', $record->php_version);
	}

	/**
	 * @testdox Statistics data is mapped for changes in 4.0
	 */
	public function testStatisticsDataIsMappedForChangesInV4(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data
		$application->input->set('php_version', '5.5.38');
		$application->input->set('db_version', '5.6.41');
		$application->input->set('cms_version', '4.0.0');
		$application->input->set('unique_id', 'a1b2c3d4');
		$application->input->set('db_type', 'mysql');
		$application->input->set('server_os', 'Darwin 14.1.0');

		/** @var SubmitDataController $controller */
		$controller = $this->kernel->getContainer()->get(SubmitDataController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(200, $response->getStatusCode());

		$this->assertSame(
			[
				'error'   => false,
				'message' => 'Data saved successfully',
			],
			$response->getPayload()
		);
	}

	/**
	 * @testdox Statistics are not submitted if required data is missing
	 */
	public function testStatisticsAreNotSubmittedIfRequiredDataIsMissing(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data
		$application->input->set('php_version', '5.5.38');
		$application->input->set('db_version', '5.6.41');
		$application->input->set('db_type', 'mysqli');
		$application->input->set('server_os', 'Darwin 14.1.0');

		/** @var SubmitDataController $controller */
		$controller = $this->kernel->getContainer()->get(SubmitDataController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(500, $response->getStatusCode());

		$this->assertSame(
			[
				'error'   => true,
				'message' => 'There was an error storing the data.',
			],
			$response->getPayload()
		);

		$this->assertTrue($this->logger->hasInfoThatContains('Missing required data from request.'));
	}

	/**
	 * @testdox Statistics are not submitted if data is invalid
	 */
	public function testStatisticsAreNotSubmittedIfDataIsInvalid(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data
		$application->input->set('php_version', '5.5.38');
		$application->input->set('db_version', '5.6.41');
		$application->input->set('cms_version', '2.5.28');
		$application->input->set('unique_id', 'a1b2c3d4');
		$application->input->set('db_type', 'mysqli');
		$application->input->set('server_os', 'Darwin 14.1.0');

		/** @var SubmitDataController $controller */
		$controller = $this->kernel->getContainer()->get(SubmitDataController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(500, $response->getStatusCode());

		$this->assertSame(
			[
				'error'   => true,
				'message' => 'Invalid data submission.',
			],
			$response->getPayload()
		);
	}
}
