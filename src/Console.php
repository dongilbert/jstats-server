<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer;

use Joomla\DI\{
	ContainerAwareInterface, ContainerAwareTrait
};

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
	public function getCommands() : array
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
	private function loadCommands() : array
	{
		$commands = [];

		/** @var \DirectoryIterator $fileInfo */
		foreach (new \DirectoryIterator(__DIR__ . '/Commands') as $fileInfo)
		{
			if ($fileInfo->isDot())
			{
				continue;
			}

			if ($fileInfo->isDir())
			{
				$namespace = $fileInfo->getFilename();

				/** @var \DirectoryIterator $subFileInfo */
				foreach (new \DirectoryIterator($fileInfo->getPathname()) as $subFileInfo)
				{
					if ($subFileInfo->isDot() || !$subFileInfo->isFile())
					{
						continue;
					}

					$command   = $subFileInfo->getBasename('.php');
					$className = __NAMESPACE__ . "\\Commands\\$namespace\\$command";

					if (!class_exists($className))
					{
						throw new \RuntimeException(sprintf('Required class "%s" not found.', $className));
					}

					// If the class isn't instantiable, it isn't a valid command
					if ((new \ReflectionClass($className))->isInstantiable())
					{
						$commands[strtolower("$namespace:" . str_replace('Command', '', $command))] = $this->getContainer()->get($className);
					}
				}
			}
			else
			{
				$command   = $fileInfo->getBasename('.php');
				$className = __NAMESPACE__ . "\\Commands\\$command";

				if (!class_exists($className))
				{
					throw new \RuntimeException(sprintf('Required class "%s" not found.', $className));
				}

				// If the class isn't instantiable, it isn't a valid command
				if ((new \ReflectionClass($className))->isInstantiable())
				{
					$commands[strtolower(str_replace('Command', '', $command))] = $this->getContainer()->get($className);
				}
			}
		}

		return $commands;
	}
}
