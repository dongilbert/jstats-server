<?php

namespace Stats\Commands\Cache;

use Joomla\Controller\AbstractController;
use Psr\Cache\CacheItemPoolInterface;
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
	 * The cache item pool.
	 *
	 * @var    CacheItemPoolInterface
	 * @since  1.0
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param   CacheItemPoolInterface  $cache  The cache item pool
	 *
	 * @since   1.0
	 */
	public function __construct(CacheItemPoolInterface $cache)
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

		$this->cache->clear();

		$this->getApplication()->out('<info>The application cache has been cleared.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription() : string
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
	public function getTitle() : string
	{
		return 'Clear Cache';
	}
}
