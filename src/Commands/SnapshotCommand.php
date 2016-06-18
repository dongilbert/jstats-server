<?php

namespace Stats\Commands;

use Joomla\Controller\AbstractController;
use Stats\CommandInterface;
use Stats\Views\Stats\StatsJsonView;

/**
 * Snapshot command
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
 *
 * @since          1.0
 */
class SnapshotCommand extends AbstractController implements CommandInterface
{
	/**
	 * JSON view for displaying the statistics.
	 *
	 * @var    StatsJsonView
	 * @since  1.0
	 */
	private $view;

	/**
	 * Constructor.
	 *
	 * @param   StatsJsonView  $view  JSON view for displaying the statistics.
	 *
	 * @since   1.0
	 */
	public function __construct(StatsJsonView $view)
	{
		$this->view  = $view;
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Creating Statistics Snapshot');

		// We want the full raw data set for our snapshot
		$this->view->isAuthorizedRaw(true);

		$file = APPROOT . '/snapshots/' . date('YmdHis');

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
	 *
	 * @since   1.0
	 */
	public function getDescription()
	{
		return 'Takes a snapshot of the statistics data.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle()
	{
		return 'Stats Snapshot';
	}
}
