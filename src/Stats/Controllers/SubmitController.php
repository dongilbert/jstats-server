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
			"db_version" => $input->getRaw("db_version"),
			"cms_version" => $input->getRaw("cms_version")
		];

		$data = array_map(
			function ($value)
			{
				return preg_replace("/[^0-9.-]/", "", $value);
			},
			$data
		);

		$data["db_type"] = $input->getCmd("db_type");
		$data["server_OS"] = $input->getString("server_OS");

		/** @var \Stats\Models\Stats $model */
		$model = $this->container->buildSharedObject("Stats\\Models\\Stats");

		$model->data = $data;

		return print_r($data, true);
	}
}
