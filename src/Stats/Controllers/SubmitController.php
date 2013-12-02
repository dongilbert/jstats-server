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

		// Filter the submitted version data.
		$data = array_map(
			function ($value)
			{
				return preg_replace("/[^0-9.-]/", "", $value);
			},
			$data
		);

		$data["unique_id"] = $input->getString("unique_id");
		$data["db_type"] = $input->getString("db_type");
		$data["server_os"] = $input->getString("server_os");

		/** @var \Stats\Tables\StatsTable $table */
		$table = $this->container->buildSharedObject("Stats\\Tables\\StatsTable");

		if (empty($data['unique_id']) || ! $table->save($data))
		{
			throw new \RuntimeException('There was an error storing the data.', 404);
		}
	}
}
