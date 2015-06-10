<?php

namespace Stats\Models;

use Joomla\Model\AbstractDatabaseModel;

class StatsModel extends AbstractDatabaseModel
{
	public function getItems()
	{
		$db = $this->getDb();
		$query = $db->getQuery(true)
			->select("*")
			->from($db->quoteName("#__jstats"))
			->group("unique_id");

		return $db->setQuery($query)->loadObjectList();
	}
}
