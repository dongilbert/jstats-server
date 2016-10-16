<?php

namespace Stats\Tests\Providers;

use Joomla\Cache\Adapter as CacheAdapter;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
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

	/**
	 * @testdox The cache service is created when caching is disabled
	 *
	 * @covers  Stats\Providers\CacheServiceProvider::getCacheService
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
	 * @covers  Stats\Providers\CacheServiceProvider::getCacheService
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
	 * @covers  Stats\Providers\CacheServiceProvider::getCacheService
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
	 * @covers  Stats\Providers\CacheServiceProvider::getCacheService
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
	 * @covers  Stats\Providers\CacheServiceProvider::getCacheService
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
	 * @covers  Stats\Providers\CacheServiceProvider::getCacheService
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
