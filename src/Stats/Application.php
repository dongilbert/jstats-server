<?php

namespace Stats;

use Joomla\Application\AbstractWebApplication;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

class Application extends AbstractWebApplication implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * @var Router
	 */
	protected $router;

	/**
	 * Execute the Application
	 *
	 * @return void
	 */
	public function doExecute()
	{
		try
		{
			$controller = $this->router->getController($this->get("uri.route"));

			$this->setBody($controller->execute());
		}
		catch (\Exception $e)
		{
			if ($e->getCode() === 404)
			{
				http_response_code(404);
			}

			$this->setBody($e->getMessage());
		}
	}

	/**
	 * @param Router $router
	 *
	 * @return $this
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;

		$this->router->setApplication($this);

		return $this;
	}
}
