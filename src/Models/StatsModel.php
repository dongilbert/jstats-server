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
	 * @return  \stdClass[]  Array of data objects.
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

			if (!in_array($column, array_keys($columnList)))
			{
				throw new \InvalidArgumentException('An invalid data source was requested.', 404);
			}
		}
		else
		{
			$column = '*';
		}

		return $db->setQuery(
			$db->getQuery(true)
				->select($column)
				->from('#__jstats')
				->group('unique_id')
		)->loadObjectList();
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
