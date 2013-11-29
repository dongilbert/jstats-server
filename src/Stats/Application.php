<?php

namespace Stats;

use Joomla\DI\Container;
use Joomla\Application\AbstractWebApplication;

class Application extends AbstractWebApplication
{
	/**
	 * @var Container
	 */
	protected $container;

	public function doExecute()
	{
		echo "Hello World!";
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
}
