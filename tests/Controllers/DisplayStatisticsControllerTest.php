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
use Joomla\StatsServer\Controllers\DisplayStatisticsController;
use Joomla\StatsServer\Kernel\WebKernel;
use Joomla\StatsServer\Tests\DatabaseManager;
use Joomla\StatsServer\Tests\DatabaseTestCase;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Test class for \Joomla\StatsServer\Controllers\DisplayStatisticsController
 */
class DisplayStatisticsControllerTest extends DatabaseTestCase
{
	/**
	 * Application kernel to use with testing
	 *
	 * @var  WebKernel
	 */
	private $kernel;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		DatabaseManager::runMigrations();
	}

	/**
	 * This method is called before each test.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		parent::setUp();

		DatabaseManager::loadExampleData();

		$this->kernel = new class(static::$connection) extends WebKernel
		{
			private $database;

			public function __construct(DatabaseInterface $database)
			{
				$this->database = $database;
			}

			protected function buildContainer(): Container
			{
				$container = parent::buildContainer();

				// Overload the database service with the test database
				$container->share(DatabaseDriver::class, $this->database);

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
		DatabaseManager::clearTables();

		parent::tearDown();
	}

	/**
	 * @testdox Sanitized statistics are returned
	 */
	public function testSanitizedStatisticsAreReturned(): void
	{
		$this->kernel->boot();

		/** @var DisplayStatisticsController $controller */
		$controller = $this->kernel->getContainer()->get(DisplayStatisticsController::class);

		$this->assertTrue($controller->execute());

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(200, $response->getStatusCode());

		$responseBody = json_decode($application->getBody(), true);

		$this->assertArrayHasKey('5.5', $responseBody['data']['php_version'], 'The response should contain the PHP version data grouped by branch.');
		$this->assertArrayNotHasKey('5.5.38', $responseBody['data']['php_version'], 'The response should contain the PHP version data grouped by branch.');
	}

	/**
	 * @testdox Unsanitized statistics are returned
	 */
	public function testUnsanitizedStatisticsAreReturned(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data and params
		$application->set('stats.rawdata', 'testing');
		$application->input->server->set('HTTP_JOOMLA_RAW', 'testing');

		/** @var DisplayStatisticsController $controller */
		$controller = $this->kernel->getContainer()->get(DisplayStatisticsController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(200, $response->getStatusCode());

		$responseBody = json_decode($application->getBody(), true);

		foreach ($responseBody['data']['php_version'] as $info)
		{
			if ($info['name'] === '5.5')
			{
				$this->fail('The response should contain the raw PHP version data.');
			}
		}
	}

	/**
	 * @testdox Statistics for a single source are returned
	 */
	public function testStatisticsForASingleSourceAreReturned(): void
	{
		$this->kernel->boot();

		/** @var WebApplication $application */
		$application = $this->kernel->getContainer()->get(AbstractApplication::class);

		// Fake the request data
		$application->input->set('source', 'php_version');

		/** @var DisplayStatisticsController $controller */
		$controller = $this->kernel->getContainer()->get(DisplayStatisticsController::class);

		$this->assertTrue($controller->execute());

		/** @var JsonResponse $response */
		$response = $application->getResponse();

		$this->assertSame(200, $response->getStatusCode());

		$responseBody = json_decode($application->getBody(), true);

		$this->assertArrayHasKey('php_version', $responseBody['data'], 'The response should only contain the PHP version data.');
		$this->assertArrayNotHasKey('cms_version', $responseBody['data'], 'The response should only contain the PHP version data.');
	}
}
