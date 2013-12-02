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
			->from("#__stats");

		return $db->setQuery($query)->loadObjectList();
	}
}
