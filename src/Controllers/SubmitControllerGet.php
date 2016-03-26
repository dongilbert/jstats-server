<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;

/**
 * Controller for processing submitted statistics data.
 *
 * @method         \Stats\WebApplication  getApplication()  Get the application object.
 * @property-read  \Stats\WebApplication  $app              Application object
 *
 * @since          1.0
 */
class SubmitControllerGet extends AbstractController
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$response = [
			'error'   => true,
			'message' => 'This route only accepts POST requests.'
		];

		$this->getApplication()->setBody(json_encode($response));

		return true;
	}
}
