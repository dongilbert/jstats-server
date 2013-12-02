<?php

namespace Stats\Controllers;

use Stats\Views\Stats\StatsHtmlView;
use Stats\Renderer\Extensions\StatsExtension;

class DisplayController extends DefaultController
{
	public function execute()
	{
		/** @var \Stats\Models\StatsModel $model */
		$model = $this->container->buildSharedObject("Stats\\Models\\StatsModel");

		$view = new StatsHtmlView($model);

		$extension = new StatsExtension;
		$extension->setApplication($this->getApplication());

		$view->getRenderer()->addExtension($extension);

		$view->setLayout("stats/stats.index");

		return $view->render();
	}
}
