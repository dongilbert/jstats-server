<?php

namespace Stats\Controllers;

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
	 * JSON view for displaying the statistics.
	 *
	 * @var    StatsJsonView
	 * @since  1.0
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   StatsJsonView  $view  JSON view for displaying the statistics.
	 *
	 * @since   1.0
	 */
	public function __construct(StatsJsonView $view)
	{
		$this->view = $view;
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

		$this->getApplication()->setBody($this->view->render());

		return true;
	}
}
