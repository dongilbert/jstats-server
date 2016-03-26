<?php

namespace Stats\Commands;

use Joomla\Application\Cli\ColorStyle;
use Joomla\Application\Cli\Output\Processor\ColorProcessor;
use Joomla\Controller\AbstractController;
use Stats\CommandInterface;

/**
 * Help command
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class HelpCommand extends AbstractController implements CommandInterface
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
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
	 *
	 * @since   1.0
	 */
	public function getDescription()
	{
		return 'Provides basic use information for the stats application.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle()
	{
		return 'Joomla Stats Application';
	}
}
