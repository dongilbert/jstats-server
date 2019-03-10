<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Models;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Model\DatabaseModelInterface;
use Joomla\Model\DatabaseModelTrait;

/**
 * Statistics database model
 */
class StatsModel implements DatabaseModelInterface
{
	use DatabaseModelTrait;

	/**
	 * Array containing the allowed sources
	 *
	 * @var  string[]
	 */
	public const ALLOWED_SOURCES = ['php_version', 'db_type', 'db_version', 'cms_version', 'server_os'];

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
	 * @return  array  An array containing the response data
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function getItems(string $column = ''): array
	{
		$db = $this->getDb();

		// Validate the requested column is actually in the table
		if ($column !== '')
		{
			// The column should exist in the table and be part of the API
			if (!\in_array($column, self::ALLOWED_SOURCES))
			{
				throw new \InvalidArgumentException('An invalid data source was requested.', 404);
			}

			return $db->setQuery(
				$db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__jstats_counter_' . $column))
			)->loadAssocList();
		}

		$return = [];

		foreach (array_keys($db->getTableColumns('#__jstats')) as $column)
		{
			// The column should exist in the table and be part of the API
			if (\in_array($column, ['unique_id', 'modified']))
			{
				continue;
			}

			$return[$column] = $db->setQuery(
				$db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__jstats_counter_' . $column))
			)->loadAssocList();
		}

		return $return;
	}

	/**
	 * Loads the recently updated statistics data from the database.
	 *
	 * Recently updated is an arbitrary 90 days, submit a pull request for a different behavior.
	 *
	 * @return  array  An array containing the response data
	 */
	public function getRecentlyUpdatedItems(): array
	{
		$db = $this->getDb();

		$return = [];

		foreach (self::ALLOWED_SOURCES as $column)
		{
			$return[$column] = $db->setQuery(
				$db->getQuery(true)
					->select($column)
					->select('COUNT(' . $column . ') AS count')
					->from($db->quoteName('#__jstats'))
					->where('modified BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND NOW()')
					->group($column)
			)->loadAssocList();
		}

		return $return;
	}

	/**
	 * Saves the given data.
	 *
	 * @param   \stdClass  $data  Data object to save.
	 *
	 * @return  void
	 */
	public function save(\stdClass $data): void
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
				->bind('unique_id', $data->unique_id, ParameterType::STRING)
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
