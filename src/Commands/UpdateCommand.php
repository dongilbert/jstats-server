<?php

namespace Stats\Commands;

use Joomla\Controller\AbstractController;
use Joomla\Database\DatabaseDriver;
use Stats\CommandInterface;

/**
 * Update command
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class UpdateCommand extends AbstractController implements CommandInterface
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
		$this->getApplication()->outputTitle('Update Server');

		$this->getApplication()->out('<info>Updating server to git HEAD</info>');

		// Pull from remote repo
		$this->runCommand('cd ' . APPROOT . ' && git pull 2>&1');

		$this->getApplication()->out('<info>Updating Composer resources</info>');

		// Run Composer install
		$this->runCommand('cd ' . APPROOT . ' && composer install --no-dev -o 2>&1');

		$this->getApplication()->out('<info>Update complete</info>');

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
		return 'Update the server to the current git HEAD.';
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
		return 'Update Server';
	}

	/**
	 * Execute a command on the server.
	 *
	 * @param   string  $command  The command to execute.
	 *
	 * @return  string  Return data from the command
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	private function runCommand($command)
	{
		$lastLine = system($command, $status);

		if ($status)
		{
			// Command exited with a status != 0
			if ($lastLine)
			{
				$this->getApplication()->out($lastLine);

				throw new \RuntimeException($lastLine);
			}

			$this->getApplication()->out('<error>An unknown error occurred</error>');

			throw new \RuntimeException('An unknown error occurred');
		}

		return $lastLine;
	}
}
