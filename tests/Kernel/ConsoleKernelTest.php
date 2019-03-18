<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Kernel;

use Joomla\Application\AbstractApplication;
use Joomla\Console\Application;
use Joomla\StatsServer\Kernel\ConsoleKernel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for \Joomla\StatsServer\Kernel\ConsoleKernel
 */
class ConsoleKernelTest extends TestCase
{
	/**
	 * @testdox The console Kernel is booted with the correct services registered
	 */
	public function testTheConsoleKernelIsBootedWithTheCorrectServicesRegistered(): void
	{
		$kernel = new class extends ConsoleKernel
		{
			public function getContainer()
			{
				return parent::getContainer();
			}
		};

		$kernel->boot();

		$this->assertTrue($kernel->isBooted());

		$container = $kernel->getContainer();

		$this->assertSame(
			$container->get(LoggerInterface::class),
			$container->get('monolog.logger.cli'),
			'The logger should be aliased to the correct service.'
		);

		$this->assertInstanceOf(
			Application::class,
			$container->get(AbstractApplication::class),
			'The AbstractApplication should be aliased to the correct subclass.'
		);
	}
}
