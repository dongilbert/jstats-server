<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Models;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Query\LimitableInterface;
use Joomla\Model\{
	DatabaseModelInterface, DatabaseModelTrait
};

/**
 * Statistics database model
 */
class StatsModel implements DatabaseModelInterface
{
	use DatabaseModelTrait;

	/**
	 * The query batch size
	 *
	 * @var  integer
	 */
	private $batchSize = 25000;

	/**
	 * Instantiate the model.
	 *
	 * @param   DatabaseDriver  $db  The database driver.
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->setDb($db);
	}

	/**
	 * Loads the statistics data from the database.
	 *
	 * @param   string  $column  A single column to filter on
	 *
	 * @return  \Generator  A Generator containing the response data
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function getItems(string $column = '') : \Generator
	{
		$db = $this->getDb();

		// To keep from running out of memory, we need to know how many records are in the database to be able to loop correctly
		$totalRecords = $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(unique_id)')
				->from('#__jstats')
		)->loadResult();

		// Validate the requested column is actually in the table
		if ($column !== '')
		{
			$columnList = $db->getTableColumns('#__jstats');

			// The column should exist in the table and be part of the API
			if (!in_array($column, array_keys($columnList)) && !in_array($column, ['unique_id', 'modified']))
			{
				throw new \InvalidArgumentException('An invalid data source was requested.', 404);
			}

			$query = $db->getQuery(true)
				->select($column);
		}
		else
		{
			$query = $db->getQuery(true)
				->select(['php_version', 'db_type', 'db_version', 'cms_version', 'server_os']);
		}

		$query->from('#__jstats')
			->group('unique_id');

		$limitable = $query instanceof LimitableInterface;

		// We can't have this as a single array, we run out of memory... This is gonna get interesting...
		for ($offset = 0; $offset < $totalRecords; $offset + $this->batchSize)
		{
			if ($limitable)
			{
				$query->setLimit($this->batchSize, $offset);

				$db->setQuery($query);
			}
			else
			{
				$db->setQuery($query, $offset, $this->batchSize);
			}

			yield $db->loadAssocList();

			$offset += $this->batchSize;
		}

		// Disconnect the DB to free some memory
		$db->disconnect();

		// And unset some variables
		unset($db, $query, $offset, $totalRecords);
	}

	/**
	 * Saves the given data.
	 *
	 * @param   \stdClass  $data  Data object to save.
	 *
	 * @return  void
	 */
	public function save(\stdClass $data)
	{
		$db = $this->getDb();

		// Set the modified date of the record
		$data->modified = (new \DateTime('now', new \DateTimeZone('UTC')))->format($db->getDateFormat());

		// Check if a row exists for this unique ID and update the existing record if so
		$recordExists = $db->setQuery(
			$db->getQuery(true)
				->select('unique_id')
				->from('#__jstats')
				->where('unique_id = :unique_id')
				->bind('unique_id', $data->unique_id, \PDO::PARAM_STR)
		)->loadResult();

		if ($recordExists)
		{
			$db->updateObject('#__jstats', $data, ['unique_id']);
		}
		else
		{
			$db->insertObject('#__jstats', $data, ['unique_id']);
		}
	}
}
