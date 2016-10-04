<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;
use Joomla\Cache\Item\Item;
use Psr\Cache\CacheItemPoolInterface;
use Stats\Views\Stats\StatsJsonView;

/**
 * Controller for displaying submitted statistics data.
 *
 * @method         \Stats\WebApplication  getApplication()  Get the application object.
 * @property-read  \Stats\WebApplication  $app              Application object
 *
 * @since          1.0
 */
class DisplayControllerGet extends AbstractController
{
	/**
	 * The cache item pool.
	 *
	 * @var    CacheItemPoolInterface
	 * @since  1.0
	 */
	private $cache;

	/**
	 * JSON view for displaying the statistics.
	 *
	 * @var    StatsJsonView
	 * @since  1.0
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   StatsJsonView           $view   JSON view for displaying the statistics.
	 * @param   CacheItemPoolInterface  $cache  The cache item pool.
	 *
	 * @since   1.0
	 */
	public function __construct(StatsJsonView $view, CacheItemPoolInterface $cache)
	{
		$this->cache = $cache;
		$this->view  = $view;
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
		// Check if we are allowed to receive the raw data
		$authorizedRaw = $this->getInput()->server->getString('HTTP_JOOMLA_RAW', 'fail') === $this->getApplication()->get('stats.rawdata', false);

		// Check if a single data source is requested
		$source = $this->getInput()->getString('source');

		$this->view->isAuthorizedRaw($authorizedRaw);
		$this->view->setSource($source);

		// Serve cached data if the cache layer is enabled and the raw data source is not requested
		if ($this->getApplication()->get('cache.enabled', false) && !$authorizedRaw)
		{
			$key = md5(get_class($this->view) . __METHOD__ . $source);

			if ($this->cache->hasItem($key))
			{
				$item = $this->cache->getItem($key);

				// Make sure we got a hit on the item, otherwise we'll have to re-cache
				if ($item->isHit())
				{
					$body = $item->get();
				}
				else
				{
					$body = $this->view->render();

					$this->cacheData($key, $body);
				}
			}
			else
			{
				$body = $this->view->render();

				$this->cacheData($key, $body);
			}
		}
		else
		{
			$body = $this->view->render();
		}

		$this->getApplication()->setBody($body);

		return true;
	}

	/**
	 * Store the given data to the cache pool.
	 *
	 * @param   string  $key   The key for the cache item.
	 * @param   mixed   $data  The data to be stored to cache.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function cacheData($key, $data)
	{
		$item = (new Item($key, $this->getApplication()->get('cache.lifetime', 900)))
			->set($data);

		$this->cache->save($item);
	}
}
