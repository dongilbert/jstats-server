<?php

namespace Stats\Controllers;

use Doctrine\Common\Cache\Cache;
use Joomla\Controller\AbstractController;
use Stats\Views\Stats\StatsJsonView;

/**
 * Controller for displaying submitted statistics data.
 *
 * @method         \Stats\Application  getApplication()  Get the application object.
 * @property-read  \Stats\Application  $app              Application object
 *
 * @since          1.0
 */
class DisplayControllerGet extends AbstractController
{
	/**
	 * The cache handler.
	 *
	 * @var    Cache
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
	 * @param   StatsJsonView  $view   JSON view for displaying the statistics.
	 * @param   Cache          $cache  The cache handler.
	 *
	 * @since   1.0
	 */
	public function __construct(StatsJsonView $view, Cache $cache)
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

			if ($this->cache->contains($key))
			{
				$body = $this->cache->fetch($key);
			}
			else
			{
				$body = $this->view->render();

				$this->cache->save($key, $body, $this->getApplication()->get('cache.lifetime', 900));
			}
		}
		else
		{
			$body = $this->view->render();
		}

		$this->getApplication()->setBody($body);

		return true;
	}
}
