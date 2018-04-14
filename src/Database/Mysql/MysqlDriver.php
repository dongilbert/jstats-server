<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Database\Mysql;

use Joomla\Database\Mysql\MysqlDriver as BaseMysqlDriver;

/**
 * Extended MySQL driver
 */
class MysqlDriver extends BaseMysqlDriver
{
	/**
	 * Method to get an array of the result set rows from the database query as a Generator where each row is an associative array
	 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
	 * a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key     The name of a field on which to key the result array.
	 * @param   string  $column  An optional column name. Instead of the whole row, only this column value will be in
	 *                           the result array.
	 *
	 * @return  \Generator|null  A Generator with the result set or null if the query failed.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function yieldAssocList($key = null, $column = null)
	{
		$this->connect();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set.
		while ($row = $this->fetchAssoc($cursor))
		{
			yield $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);
	}
}
