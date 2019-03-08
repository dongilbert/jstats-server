<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands\Snapshot;

use Joomla\Controller\AbstractController;
use Joomla\StatsServer\CommandInterface;
use Joomla\StatsServer\Views\Stats\StatsJsonView;

/**
 * Command to take a record snapshot for recently updated records
 *
 * @method         \Joomla\StatsServer\CliApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\CliApplication  $app              Application object
 */
class RecentCommand extends AbstractController implements CommandInterface
{
	/**
	 * JSON view for displaying the statistics.
	 *
	 * @var  StatsJsonView
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   StatsJsonView  $view  JSON view for displaying the statistics.
	 */
	public function __construct(StatsJsonView $view)
	{
		$this->view  = $view;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Creating Statistics Snapshot');

		// We want the full raw data set for our snapshot
		$this->view->isAuthorizedRaw(true);
		$this->view->isRecent(true);

		$file = APPROOT . '/snapshots/' . date('YmdHis') . '_recent';

		if (!file_put_contents($file, $this->view->render()))
		{
			throw new \RuntimeException('Failed writing snapshot to the filesystem at ' . $file);
		}

		$this->getApplication()->out('<info>Snapshot successfully recorded.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription(): string
	{
		return 'Takes a snapshot of the recently updated statistics data.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle(): string
	{
		return 'Recent Stats Snapshot';
	}
}
