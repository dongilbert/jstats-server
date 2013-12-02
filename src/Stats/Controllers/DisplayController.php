<?php

namespace Stats\Controllers;

use Stats\Views\Stats\StatsHtmlView;

class DisplayController extends DefaultController
{
	/**
	 * @var \Joomla\Application\AbstractWebApplication
	 */
	protected $app;

	public function execute()
	{
		/** @var \Stats\Models\StatsModel $model */
		$model = $this->container->buildSharedObject("Stats\\Models\\StatsModel");

		$view = new StatsHtmlView($model);
		$view->setLayout("stats/stats.index");

		return $view->render();
	}
}
