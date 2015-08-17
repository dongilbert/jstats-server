<?php

namespace Stats\Models;

use Joomla\Model\AbstractDatabaseModel;

class StatsModel extends AbstractDatabaseModel
{
	public function getItems()
	{
		$db = $this->getDb();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__jstats'))
				->group('unique_id')
		)->loadObjectList();
	}
}
