<?php

namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Psr\Cache\CacheItemPoolInterface;
use Stats\Providers\CacheServiceProvider;

/**
 * Test class for \Stats\Providers\CacheServiceProvider
 */
class CacheServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The cache service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\CacheServiceProvider::register
	 */
	public function testTheCacheServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new CacheServiceProvider);

		$this->assertTrue($container->exists(CacheItemPoolInterface::class));
	}
}
