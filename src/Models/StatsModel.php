<?php

namespace Stats\Models;

use Joomla\Model\AbstractDatabaseModel;

/**
 * Statistics database model
 *
 * @since  1.0
 */
class StatsModel extends AbstractDatabaseModel
{
	/**
	 * Loads the statistics data from the database.
	 *
	 * @param   string  $column  A single column to filter on
	 *
	 * @return  \array[]  Array of data arrays.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function getItems($column = null)
	{
		$db = $this->getDb();

		// Validate the requested column is actually in the table
		if ($column !== null)
		{
			$columnList = $db->getTableColumns('#__jstats');

			// The column should exist in the table and be part of the API
			if (!in_array($column, array_keys($columnList)) && !in_array($column, ['unique_id', 'modified']))
			{
				throw new \InvalidArgumentException('An invalid data source was requested.', 404);
			}

			return $db->setQuery(
				$db->getQuery(true)
					->select($column)
					->from('#__jstats')
					->group('unique_id')
			)->loadAssocList();
		}

		// If fetching all data from the table, we need to break this down a fair bit otherwise we're going to run out of memory
		$totalRecords = $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(unique_id)')
				->from('#__jstats')
		)->loadResult();

		$return = [];

		$query = $db->getQuery(true)
			->select(['php_version', 'db_type', 'db_version', 'cms_version', 'server_os'])
			->from('#__jstats')
			->group('unique_id');

		// We can't have this as a single array, we run out of memory... This is gonna get interesting...
		for ($offset = 0; $offset < $totalRecords; $offset + 25000)
		{
			$return[] = $db->setQuery($query, $offset, 25000)->loadAssocList();

			$offset += 25000;
		}

		// Disconnect the DB to free some memory
		$db->disconnect();

		// And unset some variables
		unset($db, $query, $offset, $totalRecords);

		return $return;
	}

	/**
	 * Saves the given data.
	 *
	 * @param   \stdClass  $data  Data object to save.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save($data)
	{
		$db = $this->getDb();

		// Set the modified date of the record
		$data->modified = (new \DateTime('now', new \DateTimeZone('UTC')))->format($db->getDateFormat());

		// Check if a row exists for this unique ID and update the existing record if so
		$recordExists = $db->setQuery(
			$db->getQuery(true)
				->select('unique_id')
				->from('#__jstats')
				->where('unique_id = ' . $db->quote($data->unique_id))
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
