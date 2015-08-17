<?php

namespace Stats\Controllers;

class DisplayControllerGet extends DefaultController
{
	public function execute()
	{
		/** @var \Stats\Models\StatsModel $model */
		$model = $this->getContainer()->alias('Joomla\Model\ModelInterface', 'Stats\\Models\\StatsModel')->buildSharedObject('Stats\\Models\\StatsModel');

		/** @var \Stats\Views\Stats\StatsJsonView $view */
		$view = $this->getContainer()->buildSharedObject('Stats\\Views\\Stats\\StatsJsonView');

		// Check if we are allowed to receive the raw data
		$authorizedRaw = $this->getInput()->server->getString('HTTP_JOOMLA_RAW', 'fail') === $this->getApplication()->get('stats.rawdata', false);

		$view->isAuthorizedRaw($authorizedRaw);

		$this->getApplication()->setBody($view->render());

		return true;
	}
}
