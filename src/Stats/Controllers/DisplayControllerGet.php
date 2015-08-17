<?php

namespace Stats\Controllers;

class DisplayControllerGet extends DefaultController
{
	public function execute()
	{
		/** @var \Stats\Models\StatsModel $model */
		$model = $this->getContainer()->alias('Joomla\Model\ModelInterface', 'Stats\\Models\\StatsModel')->buildSharedObject('Stats\\Models\\StatsModel');

		/** @var \Stats\Views\Stats\StatsHtmlView $view */
		$view = $this->getContainer()->buildSharedObject('Stats\\Views\\Stats\\StatsJsonView');

		return $view->render();
	}
}
