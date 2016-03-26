<?php

namespace Stats;

use Joomla\Application\Cli\ColorStyle;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;
use Joomla\Controller\AbstractController;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

/**
 * CLI Console
 *
 * @since  1.0
 */
class Console implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Array of available command objects
	 *
	 * @var    CommandInterface[]
	 * @since  1.0
	 */
	private $commands = [];

	/**
	 * Get the available commands.
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getCommands()
	{
		if (empty($this->commands))
		{
			$this->commands = $this->loadCommands();
		}

		return $this->commands;
	}

	/**
	 * Load the application's commands
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   1.0
	 */
	private function loadCommands()
	{
		$commands = [];

		/** @var \DirectoryIterator $fileInfo */
		foreach (new \DirectoryIterator(__DIR__ . '/Commands') as $fileInfo)
		{
			if ($fileInfo->isDot() || !$fileInfo->isFile())
			{
				continue;
			}

			$command   = $fileInfo->getBasename('.php');
			$className = __NAMESPACE__ . "\\Commands\\$command";

			if (false == class_exists($className))
			{
				throw new \RuntimeException(sprintf('Required class "%s" not found.', $className));
			}

			$commands[strtolower(str_replace('Command', '', $command))] = $this->getContainer()->get($className);
		}

		return $commands;
	}
}
