<?php

namespace Stats\Controllers;

class DisplayController extends DefaultController
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
