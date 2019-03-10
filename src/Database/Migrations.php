<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Database;

use Joomla\Database\DatabaseDriver;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

/**
 * Class for managing database migrations
 */
class Migrations
{
	/**
	 * Database connector
	 *
	 * @var  DatabaseDriver
	 */
	private $database;

	/**
	 * Filesystem adapter
	 *
	 * @var  Filesystem
	 */
	private $filesystem;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $database    Database connector
	 * @param   Filesystem      $filesystem  Filesystem adapter
	 */
	public function __construct(DatabaseDriver $database, Filesystem $filesystem)
	{
		$this->database   = $database;
		$this->filesystem = $filesystem;
	}

	/**
	 * Checks the migration status of the current installation
	 *
	 * @return  array
	 */
	public function checkStatus(): array
	{
		$response = ['latest' => false];

		// First get the list of applied migrations
		$appliedMigrations = $this->database->setQuery(
			$this->database->getQuery(true)
				->select('version')
				->from('#__migrations')
		)->loadColumn();

		// Now get the list of all known migrations
		$knownMigrations = [];

		foreach ($this->filesystem->listContents('migrations') as $migrationFiles)
		{
			$knownMigrations[] = $migrationFiles['filename'];
		}

		// Don't rely on file system ordering.
		sort($knownMigrations);

		// Validate all migrations are applied; the count and latest versions should match
		if (\count($appliedMigrations) === \count($knownMigrations))
		{
			$appliedValues = array_values($appliedMigrations);
			$knownValues   = array_values($knownMigrations);

			$latestApplied = (int) end($appliedValues);
			$latestKnown   = (int) end($knownValues);

			// Versions match, good to go
			if ($latestApplied === $latestKnown)
			{
				$response['latest'] = true;

				return $response;
			}
		}

		// The system is not on the latest version, get the relevant data
		$countMissing   = \count($knownMigrations) - \count($appliedMigrations);
		$currentVersion = array_pop($appliedMigrations);
		$latestVersion  = array_pop($knownMigrations);

		return array_merge(
			$response,
			[
				'missingMigrations' => $countMissing,
				'currentVersion'    => $currentVersion,
				'latestVersion'     => $latestVersion,
			]
		);
	}

	/**
	 * Migrate the database
	 *
	 * @param   string|null  $version  Optional migration version to run
	 *
	 * @return  void
	 */
	public function migrateDatabase(?string $version = null): void
	{
		// Determine the migrations to apply
		$appliedMigrations = $this->database->setQuery(
			$this->database->getQuery(true)
				->select('version')
				->from('#__migrations')
		)->loadColumn();

		// If a version is specified, check if that migration is already applied and if not, run that one only
		if ($version !== null && $version !== '')
		{
			// If it's already applied, there's nothing to do here
			if (\in_array($version, $appliedMigrations))
			{
				return;
			}

			$this->doMigration($version);

			return;
		}

		// We need to check the known migrations and filter out the applied ones to know what to do
		$knownMigrations = [];

		foreach ($this->filesystem->listContents('migrations') as $migrationFiles)
		{
			$knownMigrations[] = $migrationFiles['filename'];
		}

		foreach (array_diff($knownMigrations, $appliedMigrations) as $version)
		{
			$this->doMigration($version);
		}
	}

	/**
	 * Perform the database migration for the specified version
	 *
	 * @param   string  $version  Migration version to run
	 *
	 * @return  void
	 *
	 * @throws  FileNotFoundException
	 */
	private function doMigration(string $version): void
	{
		$sqlFile = 'migrations/' . $version . '.sql';

		if (!$this->filesystem->has($sqlFile))
		{
			throw new FileNotFoundException($sqlFile);
		}

		$queries = $this->filesystem->read($sqlFile);

		if ($queries === false)
		{
			throw new \RuntimeException(
				sprintf(
					'Could not read data from the %s SQL file, please update the database manually.',
					$sqlFile
				)
			);
		}

		foreach ($this->database->splitSql($queries) as $query)
		{
			$query = trim($query);

			if (!empty($query))
			{
				$this->database->setQuery($query)->execute();
			}
		}

		// Log the migration into the database
		$this->database->setQuery(
			$this->database->getQuery(true)
				->insert('#__migrations')
				->columns('version')
				->values($version)
		)->execute();
	}
}
