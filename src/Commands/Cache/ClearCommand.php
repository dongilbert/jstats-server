<?php

namespace Stats\Commands\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FlushableCache;
use Joomla\Controller\AbstractController;
use Stats\CommandInterface;

/**
 * CLI command for clearing the application cache
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class ClearCommand extends AbstractController implements CommandInterface
{
	/**
	 * The cache handler.
	 *
	 * @var    Cache
	 * @since  1.0
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param   Cache  $cache  The cache handler
	 *
	 * @since   1.0
	 */
	public function __construct(Cache $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Cache: Clear');

		if ($this->cache instanceof FlushableCache)
		{
			$this->cache->flushAll();

			$this->getApplication()->out('<info>The application cache has been cleared.</info>');
		}
		else
		{
			$this->getApplication()->out(
				sprintf(
					'<comment>This command only supports clearing the cache with %1$s instances that implement the %2$s interface.</comment>',
					Cache::class,
					FlushableCache::class
				)
			);
		}

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription()
	{
		return 'Clear the application cache.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle()
	{
		return 'Clear Cache';
	}
}
