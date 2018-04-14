<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Controllers;

use Joomla\Controller\AbstractController;

/**
 * Controller for displaying submitted statistics data.
 *
 * @method         \Joomla\StatsServer\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\WebApplication  $app              Application object
 */
class DisplayControllerCreate extends AbstractController
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$response = [
			'error'   => true,
			'message' => 'This route only accepts GET requests.'
		];

		// Set the response headers to indicate the method is not allowed
		$this->getApplication()->setHeader('HTTP/1.1 405 Method Not Allowed', 405, true);
		$this->getApplication()->setHeader('Allow', 'GET');

		$this->getApplication()->setBody(json_encode($response));

		return true;
	}
}
