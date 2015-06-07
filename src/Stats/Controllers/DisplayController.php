<?php

namespace Stats\Controllers;

use Stats\Views\Stats\StatsHtmlView;

class DisplayController extends DefaultController
{
	public function execute()
	{
		/** @var \Stats\Models\StatsModel $model */
		$model = $this->getContainer()->alias("Joomla\Model\ModelInterface", "Stats\\Models\\StatsModel")->buildSharedObject("Stats\\Models\\StatsModel");

		/** @var \Stats\Views\Stats\StatsHtmlView $view */
		$view = $this->getContainer()->buildSharedObject("Stats\\Views\\Stats\\StatsHtmlView");
		$view->setLayout("stats/stats.index");

		return $view->render();
	}
}
