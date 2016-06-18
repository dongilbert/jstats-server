<?php

namespace Stats\Commands\Database;

use Joomla\Controller\AbstractController;
use Stats\CommandInterface;
use Stats\Database\Migrations;

/**
 * CLI command for checking the database migration status
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
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
		return 'Check the database migration status.';
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
		return 'Database Migrations Status';
	}
}
