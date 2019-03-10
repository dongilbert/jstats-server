<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Commands;

use Joomla\Console\Application;
use Joomla\StatsServer\Commands\SnapshotRecentlyUpdatedCommand;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test class for \Joomla\StatsServer\Commands\SnapshotRecentlyUpdatedCommand
 */
class SnapshotRecentlyUpdatedCommandTest extends TestCase
{
	/**
	 * The statistics data to emulate
	 */
	private const STATS_DATA = [
		'cms_version' => [
			[
				'cms_version' => '3.5.0',
				'count'       => 3,
			],
		],
		'php_version' => [
			[
				'php_version' => PHP_VERSION,
				'count'       => 3,
			],
		],
		'db_type'     => [
			[
				'db_type' => 'mysql',
				'count'   => 1,
			],
			[
				'db_type' => 'postgresql',
				'count'   => 1,
			],
			[
				'db_type' => 'sqlsrv',
				'count'   => 1,
			],
		],
		'db_version'  => [
			[
				'db_version' => '5.6.25',
				'count'      => 1,
			],
			[
				'db_version' => '9.4.0',
				'count'      => 1,
			],
			[
				'db_version' => '10.50.2500',
				'count'      => 1,
			],
		],
		'server_os'   => [
			[
				'server_os' => 'Darwin 14.1.0',
				'count'     => 2,
			],
			[
				'server_os' => '',
				'count'     => 1,
			],
		],
	];

	/**
	 * @testdox The command creates a full snapshot
	 */
	public function testTheCommandCreatesAFullSnapshot()
	{
		/** @var MockObject|StatsJsonView $view */
		$view = $this->createMock(StatsJsonView::class);
		$view->expects($this->once())
			->method('isAuthorizedRaw')
			->with(true);

		$view->expects($this->once())
			->method('isRecent')
			->with(true);

		$view->expects($this->once())
			->method('render')
			->willReturn(json_encode(self::STATS_DATA));

		$adapter = new MemoryAdapter;

		$filesystem = new Filesystem($adapter);

		$input  = new ArrayInput(
			[
				'command' => 'snapshot:recently-updated',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new SnapshotRecentlyUpdatedCommand($view, $filesystem);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Snapshot recorded.', $screenOutput);
		$this->assertCount(1, $filesystem->listContents());
	}

	/**
	 * @testdox The command creates a full snapshot for a single source
	 */
	public function testTheCommandCreatesAFullSnapshotForASingleSource()
	{
		$source = 'db_type';

		/** @var MockObject|StatsJsonView $view */
		$view = $this->createMock(StatsJsonView::class);
		$view->expects($this->once())
			->method('isAuthorizedRaw')
			->with(true);

		$view->expects($this->once())
			->method('isRecent')
			->with(true);

		$view->expects($this->once())
			->method('setSource')
			->with($source);

		$view->expects($this->once())
			->method('render')
			->willReturn(json_encode(self::STATS_DATA[$source]));

		$adapter = new MemoryAdapter;

		$filesystem = new Filesystem($adapter);

		$input  = new ArrayInput(
			[
				'command'  => 'snapshot:recently-updated',
				'--source' => $source,
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new SnapshotRecentlyUpdatedCommand($view, $filesystem);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Snapshot recorded.', $screenOutput);
		$this->assertCount(1, $filesystem->listContents());
	}

	/**
	 * @testdox The command does not create a snapshot for an invalid source
	 */
	public function testTheCommandDoesNotCreateASnapshotForAnInvalidSource()
	{
		$this->expectException(InvalidOptionException::class);

		$source = 'bad';

		/** @var MockObject|StatsJsonView $view */
		$view = $this->createMock(StatsJsonView::class);
		$view->expects($this->once())
			->method('isAuthorizedRaw')
			->with(true);

		$view->expects($this->once())
			->method('isRecent')
			->with(true);

		$view->expects($this->never())
			->method('setSource');

		$view->expects($this->never())
			->method('render');

		$adapter = new MemoryAdapter;

		$filesystem = new Filesystem($adapter);

		$input  = new ArrayInput(
			[
				'command'  => 'snapshot:recently-updated',
				'--source' => $source,
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new SnapshotRecentlyUpdatedCommand($view, $filesystem);
		$command->setApplication($application);

		$command->execute($input, $output);
	}
}
