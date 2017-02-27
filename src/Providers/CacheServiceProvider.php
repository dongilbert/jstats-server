<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Providers;

use Joomla\Cache\{
	AbstractCacheItemPool, Adapter as CacheAdapter, CacheItemPoolInterface
};
use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;

/**
 * Cache service provider
 *
 * @since  1.0
 */
class CacheServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		$container->alias('cache', PsrCacheItemPoolInterface::class)
			->alias(CacheItemPoolInterface::class, PsrCacheItemPoolInterface::class)
			->alias(AbstractCacheItemPool::class, PsrCacheItemPoolInterface::class)
			->share(PsrCacheItemPoolInterface::class, [$this, 'getCacheService'], true);
	}

	/**
	 * Get the `cache` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  PsrCacheItemPoolInterface
	 *
	 * @since   1.0
	 */
	public function getCacheService(Container $container) : PsrCacheItemPoolInterface
	{
		/** @var \Joomla\Registry\Registry $config */
		$config = $container->get('config');

		// If caching isn't enabled then just return a void cache
		if (!$config->get('cache.enabled', false))
		{
			return new CacheAdapter\None;
		}

		$adapter = $config->get('cache.adapter', 'file');

		switch ($adapter)
		{
			case 'filesystem':
				$path = $config->get('cache.filesystem.path', 'cache');

				// If no path is given, fall back to the system's temporary directory
				if (empty($path))
				{
					$path = sys_get_temp_dir();
				}

				// If the path is relative, make it absolute... Sorry Windows users, this breaks support for your environment
				if (substr($path, 0, 1) !== '/')
				{
					$path = APPROOT . '/' . $path;
				}

				$options = [
					'file.path' => $path,
				];

				return new CacheAdapter\File($options);

			case 'none':
				return new CacheAdapter\None;

			case 'runtime':
				return new CacheAdapter\Runtime;
		}

		throw new \InvalidArgumentException(sprintf('The "%s" cache adapter is not supported.', $adapter));
	}
}
