<?php

namespace Stats\Controllers;

class HelloController extends DefaultController
{
	/**
	 * @var \Joomla\Application\AbstractWebApplication
	 */
	protected $app;

	public function execute()
	{
		return "Hello " . $this->getInput()->getCmd("name");
	}
}
