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
use Joomla\StatsServer\Providers\ConfigServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Providers\ConfigServiceProvider
 */
class ConfigServiceProviderTest extends TestCase
{
	/**
	 * @testdox The config service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\ConfigServiceProvider::__construct
	 * @covers  Joomla\StatsServer\Providers\ConfigServiceProvider::register
	 */
	public function testTheConfigServiceProviderIsRegisteredToTheContainer(): void
	{
		$container = new Container;
		$container->registerServiceProvider(new ConfigServiceProvider(APPROOT . '/etc/config.dist.json'));

		$this->assertTrue($container->exists('config'));
	}

	/**
	 * @testdox The config service provider throws an Exception if an invalid file is given
	 *
	 * @covers  Joomla\StatsServer\Providers\ConfigServiceProvider::__construct
	 * @expectedException  \RuntimeException
	 */
	public function testTheConfigServiceProviderThrowsAnExceptionIfAnInvalidFileIsGiven(): void
	{
		new ConfigServiceProvider('/bad/file/path.json');
	}

	/**
	 * @testdox The config service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\ConfigServiceProvider::getConfigService
	 */
	public function testTheConfigServiceIsCreated(): void
	{
		$this->assertInstanceOf(
			Registry::class, (new ConfigServiceProvider(APPROOT . '/etc/config.dist.json'))->getConfigService($this->createMock(Container::class))
		);
	}
}
