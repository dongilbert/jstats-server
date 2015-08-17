<?php

namespace Stats;

use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Router\RestRouter;
use Joomla\Controller\ControllerInterface;

class Router extends RestRouter implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	protected function fetchController($name)
	{
		// Derive the controller class name.
		$class = $this->controllerPrefix . ucfirst($name);

		// If the controller class does not exist panic.
		if (!class_exists($class))
		{
			throw new \RuntimeException(sprintf('Unable to locate controller `%s`.', $class), 404);
		}

		// If the controller does not follows the implementation.
		if (!is_subclass_of($class, 'Joomla\\Controller\\ControllerInterface'))
		{
			throw new \RuntimeException(
				sprintf('Invalid Controller. Controllers must implement Joomla\Controller\ControllerInterface. `%s`.', $class), 500
			);
		}

		// Instantiate the controller.
		return $this->getContainer()->get($class);
	}
}
