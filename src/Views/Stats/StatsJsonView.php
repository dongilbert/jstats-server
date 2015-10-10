<?php

namespace Stats\Views\Stats;

use Joomla\View\BaseJsonView;

/**
 * JSON response for requesting the stats data.
 *
 * @property-read  \Stats\Models\StatsModel  $model  The model object.
 *
 * @since          1.0
 */
class StatsJsonView extends BaseJsonView
{
	/**
	 * Flag if the response should return the raw data.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	private $authorizedRaw = false;

	/**
	 * The data source to return.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $source;

	/**
	 * Set whether the raw data should be returned.
	 *
	 * @param   boolean  $authorizedRaw  Flag if the response should return the raw data.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function isAuthorizedRaw($authorizedRaw)
	{
		$this->authorizedRaw = $authorizedRaw;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function render()
	{
		$items = $this->model->getItems();

		$data = [
			'php_version' => [],
			'db_type'     => [],
			'db_version'  => [],
			'cms_version' => [],
			'server_os'   => []
		];


		foreach ($items as $item)
		{
			foreach ($data as $key => $value)
			{
				if (!is_null($item->$key))
				{
					if (!isset($data[$key][$item->$key]))
					{
						$data[$key][$item->$key] = 0;
					}

					$data[$key][$item->$key]++;
				}
			}
		}

		$responseData = [];

		foreach ($data as $key => $value)
		{
			foreach ($value as $name => $count)
			{
				if ($name)
				{
					$responseData[$key][] = [
						'name'  => $name,
						'count' => $count
					];
				}
			}
		}

		$total = count($items);

		if (!$this->authorizedRaw)
		{
			foreach ($responseData as $key => $dataGroup)
			{
				switch ($key)
				{
					case 'php_version':
					case 'db_version':
						// We're going to group by minor version branch here and convert to a percentage
						$counts = [];

						foreach ($dataGroup as $row)
						{
							$version = substr($row['name'], 0, 3);

							// If the container does not exist, add it
							if (!isset($counts[$version]))
							{
								$counts[$version] = 0;
							}

							$counts[$version] += $row['count'];
						}

						$sanitizedData = [];

						foreach ($counts as $version => $count)
						{
							$sanitizedData[$version] = round($count / $total, 4) * 100;
						}

						$responseData[$key] = $sanitizedData;

						break;

					case 'server_os':
						// We're going to group by operating system here
						$counts = [];

						foreach ($dataGroup as $row)
						{
							$fullOs = explode(' ', $row['name']);
							$os     = $fullOs[0];

							if (!$os)
							{
								$os = 'unknown';
							}

							// If the container does not exist, add it
							if (!isset($counts[$os]))
							{
								$counts[$os] = 0;
							}

							$counts[$os] += $row['count'];
						}

						$sanitizedData = [];

						foreach ($counts as $os => $count)
						{
							$sanitizedData[$os] = round($count / $total, 4) * 100;
						}

						$responseData[$key] = $sanitizedData;

						break;

					case 'db_type':
					case 'cms_version':
					default:
						// For now, group by the object name and figure out the percentages
						$sanitizedData = [];

						foreach ($dataGroup as $row)
						{
							$sanitizedData[$row['name']] = round($row['count'] / $total, 4) * 100;
						}

						$responseData[$key] = $sanitizedData;

						break;
				}
			}
		}

		$responseData['total'] = $total;

		// If a partial source was requested, only return that, or throw an exception if it doesn't exist
		if ($this->source)
		{
			if (!isset($responseData[$this->source]))
			{
				throw new \InvalidArgumentException('An invalid data source was requested.', 404);
			}

			$responseData = [
				$this->source => $responseData[$this->source],
				'total'       => $total
			];
		}

		$this->addData('data', $responseData);

		return parent::render();
	}

	/**
	 * Set the data source.
	 *
	 * @param   string  $source  Data source to return.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}
}
