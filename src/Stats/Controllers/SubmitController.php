<?php

namespace Stats\Controllers;

class SubmitController extends DefaultController
{
	/**
	 * @var \Joomla\Application\AbstractWebApplication
	 */
	protected $app;

	public function execute()
	{
		$input = $this->getInput();

		$data = [
			"php_version" => $input->getRaw("php_version"),
			"mysql_version" => $input->getRaw("mysql_version")
		];

		$data = array_map(
			function ($value)
			{
				return preg_replace("/[^0-9.-]/", "", $value);
			},
			$data
		);

		/** @var \Stats\Models\Stats $model */
		$model = $this->container->buildSharedObject("Stats\\Models\\Stats");

		$model->data = $data;

		return print_r($data, true);
	}
}
