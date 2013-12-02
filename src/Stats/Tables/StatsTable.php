<?php
/**
 * Part of the Joomla Tracker Database Package
 *
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Stats\Tables;

use Joomla\Database\DatabaseDriver;

/**
 * Abstract Table class
 *
 * @since  1.0
 */
class StatsTable extends AbstractTable
{
	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   DatabaseDriver  $db     DatabaseDriver object.
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__jstats', 'id', $db);
	}

	/**
	 * Method to perform sanity checks on the AbstractTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   1.0
	 */
	public function check()
	{
		return $this;
	}
}
