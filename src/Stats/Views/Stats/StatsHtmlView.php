<?php
/**
 * Part of the Joomla Tracker's Projects Application
 *
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Stats\Views\Stats;

use Stats\Views\AbstractHtmlView;

/**
 * The projects item view
 *
 * @since  1.0
 */
class StatsHtmlView extends AbstractHtmlView
{
	/**
	 * The model object.
	 *
	 * @var    \Stats\Models\StatsModel
	 * @since  1.0
	 */
	protected $model;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   1.0
	 */
	public function render()
	{
		$items = $this->model->getItems();

		$data = [
			'php_version' => [],
			'db_type' => [],
			'db_version' => [],
			'cms_version' => [],
			'server_os' => []
		];

		foreach ($items as $item)
		{
			foreach ($data as $key => $value)
			{
				if (! isset($data[$key][$item->{$key}]))
				{
					$data[$key][$item->{$key}] = 0;
				}

				$data[$key][$item->{$key}]++;
			}
		}

		$tmp = [];

		foreach ($data as $key => $value)
		{
			foreach ($value as $name => $count)
			{
				$tmp[$key][] = [
					'name' => $name,
					'count' => $count
				];
			}
		}

		$data = $tmp;

		$this->renderer->set('data', $data);

		return parent::render();
	}
}
