<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer;

use Joomla\Application\AbstractWebApplication;
use Ramsey\Uuid\Uuid;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Web application for the stats server
 */
class WebApplication extends AbstractWebApplication
{
	/**
	 * Application analytics object.
	 *
	 * @var  Analytics
	 */
	private $analytics;

	/**
	 * Response mime type.
	 *
	 * @var  string
	 */
	public $mimeType = 'application/json';

	/**
	 * Application router.
	 *
	 * @var  Router
	 */
	private $router;

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		// On a GET request to the live domain, submit analytics data
		if ($this->input->getMethod() === 'GET'
			&& strpos($this->input->server->getString('HTTP_HOST', ''), 'developer.joomla.org') === 0
			&& $this->analytics)
		{
			$this->analytics->setAsyncRequest(true)
				->setProtocolVersion('1')
				->setTrackingId('UA-544070-16')
				->setClientId(Uuid::uuid4()->toString())
				->setDocumentPath($this->get('uri.base.path'))
				->setIpOverride($this->input->server->getString('REMOTE_ADDR', '127.0.0.1'))
				->setUserAgentOverride($this->input->server->getString('HTTP_USER_AGENT', 'JoomlaStats/1.0'));

			// Don't allow sending Analytics data to cause a failure
			try
			{
				$this->analytics->sendPageview();
			}
			catch (\Exception $e)
			{
				// Log the error for reference
				$this->getLogger()->error(
					'Error sending analytics data.',
					['exception' => $e]
				);
			}
		}

		try
		{
			$this->router->getController($this->get('uri.route'))->execute();
		}
		catch (\Throwable $e)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Uncaught Throwable of type %s caught.', get_class($e)),
				['exception' => $e]
			);

			$this->setErrorHeader($e);

			$data = [
				'error'   => true,
				'message' => $e->getMessage(),
			];

			$this->setBody(json_encode($data));
		}
	}

	/**
	 * Set the application's analytics object.
	 *
	 * @param   Analytics  $analytics  Analytics object to set.
	 *
	 * @return  $this
	 */
	public function setAnalytics(Analytics $analytics) : WebApplication
	{
		$this->analytics = $analytics;

		return $this;
	}

	/**
	 * Set the HTTP Response Header for error conditions.
	 *
	 * @param   \Throwable  $exception  The Throwable object to process.
	 *
	 * @return  void
	 */
	private function setErrorHeader(\Throwable $exception)
	{
		switch ($exception->getCode())
		{
			case 401:
				$this->setHeader('HTTP/1.1 401 Unauthorized', 401, true);

				break;

			case 403:
				$this->setHeader('HTTP/1.1 403 Forbidden', 403, true);

				break;

			case 404:
				$this->setHeader('HTTP/1.1 404 Not Found', 404, true);

				break;

			case 500:
			default:
				$this->setHeader('HTTP/1.1 500 Internal Server Error', 500, true);

				break;
		}
	}

	/**
	 * Set the application's router.
	 *
	 * @param   Router  $router  Router object to set.
	 *
	 * @return  $this
	 */
	public function setRouter(Router $router) : WebApplication
	{
		$this->router = $router;

		return $this;
	}
}
