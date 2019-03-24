<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Commands\Database;

use Joomla\Console\Application;
use Joomla\StatsServer\Commands\Database\MigrationStatusCommand;
use Joomla\StatsServer\Database\Migrations;
use Joomla\StatsServer\Database\MigrationsStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test class for \Joomla\StatsServer\Commands\Database\MigrationStatusCommand
 */
class MigrationStatusCommandTest extends TestCase
{
	/**
	 * @testdox The command shows the status when the table does not exist
	 */
	public function testTheCommandShowsTheStatusWhenTheTableDoesNotExist(): void
	{
		$status              = new MigrationsStatus;
		$status->tableExists = false;

		/** @var MockObject|Migrations $migrations */
		$migrations = $this->createMock(Migrations::class);
		$migrations->expects($this->once())
			->method('checkStatus')
			->willReturn($status);

		$input  = new ArrayInput(
			[
				'command' => 'database:migrations:status',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new MigrationStatusCommand($migrations);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('The migrations table does not exist', $screenOutput);
	}

	/**
	 * @testdox The command shows the status when on the latest version
	 */
	public function testTheCommandShowsTheStatusWhenOnTheLatestVersion(): void
	{
		$status         = new MigrationsStatus;
		$status->latest = true;

		/** @var MockObject|Migrations $migrations */
		$migrations = $this->createMock(Migrations::class);
		$migrations->expects($this->once())
			->method('checkStatus')
			->willReturn($status);

		$input  = new ArrayInput(
			[
				'command' => 'database:migrations:status',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new MigrationStatusCommand($migrations);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Your database is up-to-date.', $screenOutput);
	}

	/**
	 * @testdox The command shows the status when not on the latest version
	 */
	public function testTheCommandShowsTheStatusWhenNotOnTheLatestVersion(): void
	{
		$status                    = new MigrationsStatus;
		$status->currentVersion    = '1';
		$status->latest            = false;
		$status->latestVersion     = '2';
		$status->missingMigrations = 1;

		/** @var MockObject|Migrations $migrations */
		$migrations = $this->createMock(Migrations::class);
		$migrations->expects($this->once())
			->method('checkStatus')
			->willReturn($status);

		$input  = new ArrayInput(
			[
				'command' => 'database:migrations:status',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new MigrationStatusCommand($migrations);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Your database is not up-to-date. You are missing 1 migration(s).', $screenOutput);
	}
}
