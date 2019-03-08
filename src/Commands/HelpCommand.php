<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands;

use Joomla\Application\Cli\ColorStyle;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;
use Joomla\Controller\AbstractController;
use Joomla\StatsServer\CommandInterface;

/**
 * Help command
 *
 * @method         \Joomla\StatsServer\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\CliApplication  $app              Application object
 */
class HelpCommand extends AbstractController implements CommandInterface
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		/** @var ColorProcessor $processor */
		$processor = $this->getApplication()->getOutput()->getProcessor();
		$processor->addStyle('cmd', new ColorStyle('magenta'));

		$executable = basename($this->getApplication()->input->executable);

		$commands = $this->getApplication()->getConsole()->getCommands();

		$this->getApplication()->outputTitle($this->getTitle());

		$this->getApplication()->out(
			sprintf('Usage: <info>%s</info> <cmd><command></cmd>',
				$executable
			)
		);

		$this->getApplication()->out()
			->out('Available commands:')
			->out();

		foreach ($this->getApplication()->getConsole()->getCommands() as $cName => $command)
		{
			$this->getApplication()->out('<cmd>' . $cName . '</cmd>');

			if ($command->getDescription())
			{
				$this->getApplication()->out('    ' . $command->getDescription());
			}

			$this->getApplication()->out();
		}

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription(): string
	{
		return 'Provides basic use information for the stats application.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle(): string
	{
		return 'Joomla Stats Application';
	}
}
