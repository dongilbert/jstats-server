<?php

namespace Stats\Providers;

use Doctrine\Common\Cache;
use Doctrine\Common\Cache\Cache as CacheInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

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
		$container->alias('cache', CacheInterface::class)
			->share(
				CacheInterface::class,
				function (Container $container)
				{
					/** @var \Joomla\Registry\Registry $config */
					$config = $container->get('config');

					// If caching isn't enabled then just return a void cache
					if (!$config->get('cache.enabled', false))
					{
						return new Cache\VoidCache;
					}

					$adapter = $config->get('cache.adapter', 'filesystem');

					switch ($adapter)
					{
						case 'array':
							$handler = new Cache\ArrayCache;

							break;

						case 'filesystem':
							$path = $config->get('cache.filesystem.path', 'cache');

							// If no path is given, fall back to the system's temporary directory
							if (empty($path))
							{
								$path = sys_get_temp_dir();
							}

							// If the path is relative, make it absolute
							if (substr($path, 0, 1) !== '/')
							{
								$path = APPROOT . '/' . $path;
							}

							$handler = new Cache\FilesystemCache($path);

							break;

						case 'phpfile':
							$path = $config->get('cache.phpfile.path', 'cache');

							// If no path is given, fall back to the system's temporary directory
							if (empty($path))
							{
								$path = sys_get_temp_dir();
							}

							// If the path is relative, make it absolute
							if (substr($path, 0, 1) !== '/')
							{
								$path = APPROOT . '/' . $path;
							}

							$handler = new Cache\PhpFileCache($path);

							break;

						default:
							throw new \InvalidArgumentException(sprintf('The "%s" cache adapter is not supported.', $adapter));
					}

					return $handler;
				},
				true
			);
	}
}
