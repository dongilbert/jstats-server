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

		// Set the response headers to indicate the method is not allowed
		$this->getApplication()->setHeader('HTTP/1.1 405 Method Not Allowed', 405, true);
		$this->getApplication()->setHeader('Allow', 'POST');

		$this->getApplication()->setBody(json_encode($response));

		return true;
	}
}
