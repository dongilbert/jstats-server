<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;
use Stats\Models\StatsModel;

/**
 * Controller for processing submitted statistics data.
 *
 * @method         \Stats\Application  getApplication()  Get the application object.
 * @property-read  \Stats\Application  $app              Application object
 *
 * @since          1.0
 */
class SubmitControllerCreate extends AbstractController
{
	/**
	 * Statistics model object.
	 *
	 * @var    StatsModel
	 * @since  1.0
	 */
	private $model;

	/**
	 * Constructor.
	 *
	 * @param   StatsModel  $model  Statistics model object.
	 *
	 * @since   1.0
	 */
	public function __construct(StatsModel $model)
	{
		$this->model = $model;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
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
				return preg_replace('/[^0-9.]/', '', $value);
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
