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
use Psr\Log\{
	LoggerAwareInterface, LoggerAwareTrait
};

/**
 * CLI command for migrating the database
 *
 * @method         \Joomla\StatsServer\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class MigrateCommand extends AbstractController implements CommandInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;

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
		$this->getApplication()->outputTitle('Database Migrations: Migrate');

		// If a version is given, we are only executing that migration
		$version = $this->getApplication()->input->getString('version', $this->getApplication()->input->getString('v', ''));

		try
		{
			$this->migrations->migrateDatabase($version);
		}
		catch (\Exception $exception)
		{
			$this->logger->critical(
				'Error migrating database',
				['exception' => $exception]
			);

			$message = sprintf('Error migrating database: %s', $exception->getMessage());

			$this->getApplication()->out("<error>$message</error>");

			return false;
		}

		$this->logger->info('Database migrated to latest version.');

		$this->getApplication()->out('<info>Database migrated to latest version.</info>');

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
		return 'Migrate the database schema to a newer version.';
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
		return 'Database Migrations';
	}
}
