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
				->from('#__jstats')
				->group('unique_id')
		)->loadObjectList();
	}

	public function save($data)
	{
		$db = $this->getDb();

		// Check if a row exists for this unique ID and update the existing record if so
		$recordExists = $db->setQuery(
			$db->getQuery(true)
				->select('id')
				->from('#__jstats')
				->where('unique_id = ' . $db->quote($data->unique_id))
		)->loadResult();

		if ($recordExists)
		{
			$data->id = $recordExists;
			$db->updateObject('#__jstats', $data, ['id']);
		}
		else
		{
			$db->insertObject('#__jstats', $data, ['id']);
		}
	}
}
