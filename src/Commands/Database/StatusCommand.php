<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands\Database;

use Joomla\Controller\AbstractController;
use Joomla\StatsServer\CommandInterface;
use Joomla\StatsServer\Database\Migrations;

/**
 * CLI command for checking the database migration status
 *
 * @method         \Joomla\StatsServer\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class StatusCommand extends AbstractController implements CommandInterface
{
	/**
	 * Database migrations helper
	 *
	 * @var    Migrations
	 * @since  1.0
	 */
	private $migrations;

	/**
	 * Constructor.
	 *
	 * @param   Migrations  $migrations  Database migrations helper
	 *
	 * @since   1.0
	 */
	public function __construct(Migrations $migrations)
	{
		$this->migrations = $migrations;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Database Migrations: Check Status');

		$status = $this->migrations->checkStatus();

		if ($status['latest'])
		{
			$this->getApplication()->out('<fg=green;options=bold>Your database is up-to-date.</fg=green;options=bold>');
		}
		else
		{
			$this->getApplication()->out(
				'<comment>' . sprintf('Your database is not up-to-date. You are missing %d migrations.', $status['missingMigrations']) . '</comment>'
			)
				->out()
				->out('<comment>' . sprintf('Current Version: %1$s', $status['currentVersion']) . '</comment>')
				->out('<comment>' . sprintf('Latest Version: %1$s', $status['latestVersion']) . '</comment>')
				->out()
				->out(sprintf('To update, run the <fg=magenta>%1$s</fg=magenta> command.', 'database:migrate'));
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
	public function getDescription() : string
	{
		return 'Check the database migration status.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle() : string
	{
		return 'Database Migrations Status';
	}
}
