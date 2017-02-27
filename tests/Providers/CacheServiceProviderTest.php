<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\Cache\Adapter as CacheAdapter;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Joomla\StatsServer\Providers\CacheServiceProvider;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Test class for \Joomla\StatsServer\Providers\CacheServiceProvider
 */
class CacheServiceProviderTest extends TestCase
{
	/**
	 * @testdox The cache service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::register
	 */
	public function testTheCacheServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new CacheServiceProvider);

		$this->assertTrue($container->exists(CacheItemPoolInterface::class));
	}

	/**
	 * @testdox The cache service is created when caching is disabled
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::getCacheService
	 */
	public function testTheCacheServiceIsCreatedWhenCachingIsDisabled()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->once())
			->method('get')
			->with('cache.enabled')
			->willReturn(false);

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(CacheAdapter\None::class, (new CacheServiceProvider)->getCacheService($mockContainer));
	}

	/**
	 * @testdox The cache service is created for the no-op cache
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::getCacheService
	 */
	public function testTheCacheServiceIsCreatedForTheNoOpCache()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls(true, 'none');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(CacheAdapter\None::class, (new CacheServiceProvider)->getCacheService($mockContainer));
	}

	/**
	 * @testdox The cache service is created for the filesystem cache with a relative path
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::getCacheService
	 */
	public function testTheCacheServiceIsCreatedForTheFilesystemCacheWithARelativePath()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$this->markTestSkipped('Filesystems are not correctly handled for Windows');
		}

		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(3))
			->method('get')
			->willReturnOnConsecutiveCalls(true, 'filesystem', 'cache');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(CacheAdapter\File::class, (new CacheServiceProvider)->getCacheService($mockContainer));
	}

	/**
	 * @testdox The cache service is created for the filesystem cache with an empty path
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::getCacheService
	 */
	public function testTheCacheServiceIsCreatedForTheFilesystemCacheWithAnEmptyPath()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$this->markTestSkipped('Filesystems are not correctly handled for Windows');
		}

		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(3))
			->method('get')
			->willReturnOnConsecutiveCalls(true, 'filesystem', '');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(CacheAdapter\File::class, (new CacheServiceProvider)->getCacheService($mockContainer));
	}

	/**
	 * @testdox The cache service is created for the runtime cache
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::getCacheService
	 */
	public function testTheCacheServiceIsCreatedForTheRuntimeCache()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls(true, 'runtime');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(CacheAdapter\Runtime::class, (new CacheServiceProvider)->getCacheService($mockContainer));
	}

	/**
	 * @testdox The cache service is not created for an unsupported adapter
	 *
	 * @covers  Joomla\StatsServer\Providers\CacheServiceProvider::getCacheService
	 *
	 * @expectedException  \InvalidArgumentException
	 * @expectedExceptionMessage  The "invalid" cache adapter is not supported.
	 */
	public function testTheCacheServiceIsNotCreatedForAnUnsupportedAdapter()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->exactly(2))
			->method('get')
			->willReturnOnConsecutiveCalls(true, 'invalid');

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		(new CacheServiceProvider)->getCacheService($mockContainer);
	}
}
