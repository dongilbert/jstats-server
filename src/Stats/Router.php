<?php

namespace Stats;

use Joomla\Router\RestRouter;
use Joomla\Controller\ControllerInterface;
use Joomla\DI\ContainerAwareInterface;

class Router extends RestRouter
{
	/**
	 * @var Application
	 */
	public $app;

	/**
	 * Get a JController object for a given name.
	 *
	 * @param   string  $name  The controller name (excluding prefix) for which to fetch and instance.
	 *
	 * @return  ControllerInterface
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function fetchController($name)
	{
		/** @var \Stats\Controllers\DefaultController $controller */
		$controller = parent::fetchController($name);

		if ($controller instanceof ContainerAwareInterface)
		{
			$controller->setContainer($this->app->getContainer());
		}

		if ($controller instanceof ControllerInterface)
		{
			$controller->setApplication($this->app);
		}

		return $controller;
	}

	public function setApplication(Application $app)
	{
		$this->app = $app;

		return $this;
	}
}
