<?php

namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Stats\Providers\MonologServiceProvider;

/**
 * Test class for \Stats\Providers\MonologServiceProvider
 */
class MonologServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The Monolog service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\MonologServiceProvider::register
	 */
	public function testTheDatabaseServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new MonologServiceProvider);

		$this->assertTrue($container->exists('monolog.logger.database'));
	}
}
