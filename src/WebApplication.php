<?php

namespace Stats;

use Joomla\Application\AbstractWebApplication;

/**
 * Web application for the stats server
 *
 * @since  1.0
 */
class WebApplication extends AbstractWebApplication
{
	/**
	 * Response mime type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $mimeType = 'application/json';

	/**
	 * Application router.
	 *
	 * @var    Router
	 * @since  1.0
	 */
	private $router;

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		try
		{
			$this->router->getController($this->get('uri.route'))->execute();
		}
		catch (\Exception $e)
		{
			// Log the error for reference
			$this->getLogger()->error(
				sprintf('Uncaught Exception of type %s caught.', get_class($e)),
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
	 * Set the HTTP Response Header for error conditions.
	 *
	 * @param   \Exception  $exception  The Exception object to process.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function setErrorHeader(\Exception $exception)
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
	 *
	 * @since   1.0
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;

		return $this;
	}
}
