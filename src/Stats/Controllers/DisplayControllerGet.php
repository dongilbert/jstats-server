<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;
use Stats\Views\Stats\StatsJsonView;

/**
 * @method         \Stats\Application  getApplication()  Get the application object.
 * @property-read  \Stats\Application  $app              Application object
 */
class DisplayControllerGet extends AbstractController
{
	/**
	 * @var StatsJsonView
	 */
	private $view;

	public function __construct(StatsJsonView $view)
	{
		$this->view = $view;
	}

	public function execute()
	{
		// Check if we are allowed to receive the raw data
		$authorizedRaw = $this->getInput()->server->getString('HTTP_JOOMLA_RAW', 'fail') === $this->getApplication()->get('stats.rawdata', false);

		$this->view->isAuthorizedRaw($authorizedRaw);

		$this->getApplication()->setBody($this->view->render());

		return true;
	}
}
