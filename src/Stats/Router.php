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
