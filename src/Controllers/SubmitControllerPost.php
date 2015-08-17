<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;
use Stats\Models\StatsModel;

/**
 * @method         \Stats\Application  getApplication()  Get the application object.
 * @property-read  \Stats\Application  $app              Application object
 */
class SubmitControllerPost extends AbstractController
{
	/**
	 * @var StatsModel
	 */
	private $model;

	public function __construct(StatsModel $model)
	{
		$this->model = $model;
	}

	public function execute()
	{
		$input = $this->getInput();

		$data = [
			'php_version' => $input->getRaw('php_version'),
			'db_version'  => $input->getRaw('db_version'),
			'cms_version' => $input->getRaw('cms_version')
		];

		// Filter the submitted version data.
		$data = array_map(
			function ($value)
			{
				return preg_replace('/[^0-9.-]/', '', $value);
			},
			$data
		);

		$data['unique_id'] = $input->getString('unique_id');
		$data['db_type']   = $input->getString('db_type');
		$data['server_os'] = $input->getString('server_os');

		// We require at a minimum a unique ID and the CMS version
		if (empty($data['unique_id']) || empty($data['cms_version']))
		{
			throw new \RuntimeException('There was an error storing the data.', 401);
		}

		$this->model->save((object) $data);

		$response = [
			'error'   => false,
			'message' => 'Data saved successfully'
		];

		$this->getApplication()->setBody(json_encode($response));

		return true;
	}
}
