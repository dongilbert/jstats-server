<?php

namespace Stats;

use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\Application\AbstractWebApplication;

class Application extends AbstractWebApplication implements ContainerAwareInterface
{
	/**
	 * @var Container
	 */
	protected $container;

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
	 * Set the application DI Container
	 *
	 * @param Container $container
	 *
	 * @return Application
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
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
