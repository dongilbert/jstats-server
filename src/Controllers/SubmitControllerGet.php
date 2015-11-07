<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;

/**
 * Controller for processing submitted statistics data.
 *
 * @method         \Stats\Application  getApplication()  Get the application object.
 * @property-read  \Stats\Application  $app              Application object
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
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		throw new \RuntimeException('This route only accepts POST requests.');
	}
}
