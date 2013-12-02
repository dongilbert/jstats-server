<?php

namespace Stats\Controllers;

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

		$model->getItems();

		return print_r($items, true);
	}
}
