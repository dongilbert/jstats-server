<?php

namespace Stats;

use Joomla\Application\AbstractWebApplication;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

class Application extends AbstractWebApplication implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	public $mimeType = 'application/json';

	/**
	 * @var Router
	 */
	protected $router;

	public function doExecute()
	{
		try
		{
			$controller = $this->router->getController($this->get('uri.route'));
			$controller->execute();
		}
		catch (\Exception $e)
		{
			$this->setErrorHeader($e);

			$data = [
				'error'   => true,
				'message' => $e->getMessage(),
			];

			$this->setBody(json_encode($data));
		}
	}

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

	public function setRouter(Router $router)
	{
		$this->router = $router;

		return $this;
	}
}
