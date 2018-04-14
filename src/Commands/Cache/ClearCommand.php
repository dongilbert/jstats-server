<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands\Cache;

use Joomla\Controller\AbstractController;
use Joomla\StatsServer\CommandInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * CLI command for clearing the application cache
 *
 * @method         \Joomla\StatsServer\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\CliApplication  $app              Application object
 */
class ClearCommand extends AbstractController implements CommandInterface
{
	/**
	 * The cache item pool.
	 *
	 * @var  CacheItemPoolInterface
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param   CacheItemPoolInterface  $cache  The cache item pool
	 */
	public function __construct(CacheItemPoolInterface $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
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
	 */
	public function getDescription() : string
	{
		return 'Clear the application cache.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string
	{
		return 'Clear Cache';
	}
}
