<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands;

use Joomla\Controller\AbstractController;
use Joomla\StatsServer\CommandInterface;

/**
 * Update command
 *
 * @method         \Joomla\StatsServer\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\CliApplication  $app              Application object
 */
class UpdateCommand extends AbstractController implements CommandInterface
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Update Server');

		$this->getApplication()->out('<info>Updating server to git HEAD</info>');

		// Pull from remote repo
		$this->runCommand('cd ' . APPROOT . ' && git pull 2>&1');

		$this->getApplication()->out('<info>Updating Composer resources</info>');

		// Run Composer install
		$this->runCommand('cd ' . APPROOT . ' && composer install --no-dev -o -a 2>&1');

		$this->getApplication()->out('<info>Update complete</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription() : string
	{
		return 'Update the server to the current git HEAD.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string
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
	 * @throws  \RuntimeException
	 */
	private function runCommand(string $command) : string
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
