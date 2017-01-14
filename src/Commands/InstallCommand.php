<?php

namespace Stats\Commands;

use Joomla\Controller\AbstractController;
use Joomla\Database\DatabaseDriver;
use Stats\CommandInterface;

/**
 * Install command
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class InstallCommand extends AbstractController implements CommandInterface
{
	/**
	 * Database driver.
	 *
	 * @var    DatabaseDriver
	 * @since  1.0
	 */
	private $db = null;

	/**
	 * Constructor.
	 *
	 * @param   DatabaseDriver  $db  Database driver.
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->db = $db;
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
		$this->getApplication()->outputTitle('Installer');

		try
		{
			// Check if the database "exists"
			$tables = $this->db->getTableList();

			if (!$this->getApplication()->input->getBool('reinstall', false))
			{
				$this->out()
					->out('<fg=black;bg=yellow>WARNING: A database has been found !!</fg=black;bg=yellow>')
					->out()
					->out('Do you want to reinstall ?')
					->out()
					->out('1) Yes')
					->out('2) No')
					->out()
					->out('<question>' . g11n3t('Select:') . '</question>', false);

				$in = trim($this->getApplication()->in());

				if ((int) $in !== 1)
				{
					$this->out('<info>Aborting installation.</info>');

					return true;
				}
			}

			$this->cleanDatabase($tables);
		}
		catch (\RuntimeException $e)
		{
			// Check if the message is "Could not connect to database."  Odds are, this means the DB isn't there or the server is down.
			if (strpos($e->getMessage(), 'Could not connect to database.') !== false)
			{
				$this->out('No database found.')
					->out('Creating the database...', false);

				$this->db->setQuery('CREATE DATABASE ' . $this->db->quoteName($this->getApplication()->get('database.name')))
					->execute();

				$this->db->select($this->getApplication()->get('database.name'));

				$this->out('<info>Database created.</info>');
			}
			else
			{
				throw $e;
			}
		}

		// Perform the installation
		$this->processSql()
			->out()
			->out('<fg=green;options=bold>Installation has been completed successfully.</fg=green;options=bold>');

		return true;
	}

	/**
	 * Cleanup the database.
	 *
	 * @param   array  $tables  Tables to remove.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	private function cleanDatabase(array $tables) : InstallCommand
	{
		$this->out('Removing existing tables...', false);

		// Foreign key constraint fails fix
		$this->db->setQuery('SET FOREIGN_KEY_CHECKS=0')
			->execute();

		foreach ($tables as $table)
		{
			$this->db->dropTable($table, true);
			$this->out('.', false);
		}

		$this->db->setQuery('SET FOREIGN_KEY_CHECKS=1')
			->execute();

		$this->out('<info>Tables removed.</info>');

		return $this;
	}

	/**
	 * Process the main SQL file.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException
	 */
	private function processSql() : InstallCommand
	{
		// Install.
		$dbType = $this->getApplication()->get('database.driver');

		if ('mysqli' == $dbType)
		{
			$dbType = 'mysql';
		}

		$fName = APPROOT . '/etc/' . $dbType . '.sql';

		if (!file_exists($fName))
		{
			throw new \UnexpectedValueException(sprintf('Install SQL file for %s not found.', $dbType));
		}

		$sql = file_get_contents($fName);

		if (!$sql)
		{
			throw new \UnexpectedValueException('SQL file corrupted.');
		}

		$this->out(sprintf('Creating tables from file %s', realpath($fName)), false);

		foreach ($this->db->splitSql($sql) as $query)
		{
			$q = trim($this->db->replacePrefix($query));

			if ('' == trim($q))
			{
				continue;
			}

			$this->db->setQuery($q)
				->execute();

			$this->out('.', false);
		}

		$this->out('<info>Database tables created successfully.</info>');

		return $this;
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
		return 'Installs the application.';
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
		return 'Install Application';
	}
}
