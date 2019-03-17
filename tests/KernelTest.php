<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests;

use Joomla\Application\AbstractApplication;
use Joomla\DI\Container;
use Joomla\StatsServer\Kernel;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Joomla\StatsServer\Kernel
 */
class KernelTest extends TestCase
{
	/**
	 * @testdox The Kernel is run
	 */
	public function testTheKernelIsRun()
	{
		$application = new class extends AbstractApplication
		{
			private $executed = false;

			protected function doExecute()
			{
				$this->executed = true;
			}

			public function isExecuted(): bool
			{
				return $this->executed === true;
			}
		};

		$kernel = new class($application) extends Kernel
		{
			private $application;

			public function __construct(AbstractApplication $application)
			{
				$this->application = $application;
			}

			protected function buildContainer(): Container
			{
				$container = parent::buildContainer();

				$container->share(AbstractApplication::class, $this->application, true);

				$container->alias('monolog', 'monolog.logger.application')
					->alias('logger', 'monolog.logger.application')
					->alias(Logger::class, 'monolog.logger.application')
					->alias(LoggerInterface::class, 'monolog.logger.application');

				return $container;
			}
		};

		$kernel->run();

		$this->assertTrue($application->isExecuted());
	}

	/**
	 * @testdox The Kernel is not run when an application is not registered
	 */
	public function testTheKernelIsNotRunWhenAnApplicationIsNotRegistered()
	{
		$kernel = new class extends Kernel
		{
			protected function buildContainer(): Container
			{
				$container = parent::buildContainer();

				$container->alias('monolog', 'monolog.logger.application')
					->alias('logger', 'monolog.logger.application')
					->alias(Logger::class, 'monolog.logger.application')
					->alias(LoggerInterface::class, 'monolog.logger.application');

				return $container;
			}
		};

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('The application has not been registered with the container.');

		$kernel->run();
	}
}
