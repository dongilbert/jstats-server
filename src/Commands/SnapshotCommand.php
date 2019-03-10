<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands;

use Joomla\Console\Command\AbstractCommand;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to take a record snapshot
 */
class SnapshotCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var  string|null
	 */
	protected static $defaultName = 'snapshot';

	/**
	 * JSON view for displaying the statistics.
	 *
	 * @var  StatsJsonView
	 */
	private $view;

	/**
	 * Filesystem adapter for the snapshots space.
	 *
	 * @var  Filesystem
	 */
	private $filesystem;

	/**
	 * Constructor.
	 *
	 * @param   StatsJsonView  $view        JSON view for displaying the statistics.
	 * @param   Filesystem     $filesystem  Filesystem adapter for the snapshots space.
	 */
	public function __construct(StatsJsonView $view, Filesystem $filesystem)
	{
		$this->view       = $view;
		$this->filesystem = $filesystem;

		parent::__construct();
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Creating Statistics Snapshot');

		// We want the full raw data set for our snapshot
		$this->view->isAuthorizedRaw(true);

		if (!$this->filesystem->write(date('YmdHis'), $this->view->render()))
		{
			$symfonyStyle->error('Failed writing snapshot to the filesystem.');

			return 1;
		}

		$symfonyStyle->success('Snapshot recorded.');

		return 0;
	}

	/**
	 * Configures the current command.
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->setDescription('Takes a snapshot of the statistics data.');
	}
}
