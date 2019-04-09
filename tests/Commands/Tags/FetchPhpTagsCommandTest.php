<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Commands\Tags;

use Joomla\Console\Application;
use Joomla\Http\Response;
use Joomla\StatsServer\Commands\Tags\FetchPhpTagsCommand;
use Joomla\StatsServer\GitHub\GitHub;
use Joomla\StatsServer\GitHub\Package\Repositories;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test class for \Joomla\StatsServer\Commands\Tags\FetchPhpTagsCommand
 */
class FetchPhpTagsCommandTest extends TestCase
{
	/**
	 * @testdox The command fetches the release tags from the PHP repository with no extra pages
	 */
	public function testTheCommandFetchesTheReleaseTagsFromThePhpRepositoryWithNoExtraPages(): void
	{
		$response = new Response;

		$githubRepositories = new class extends Repositories
		{
			public function getApiResponse()
			{
				return new Response;
			}

			public function getTags($owner, $repo, $page = 0)
			{
				return [
					(object) [
						'name' => 'php-7.1.0',
					],
					(object) [
						'name' => 'php-7.1.1',
					],
					(object) [
						'name' => 'php-7.1.2',
					],
					(object) [
						'name' => 'php-7.1.3',
					],
					(object) [
						'name' => 'php-7.1.4',
					],
					(object) [
						'name' => 'php-7.2.0',
					],
					(object) [
						'name' => 'php-7.2.1',
					],
					(object) [
						'name' => 'php-7.2.2',
					],
					(object) [
						'name' => 'php-7.2.3',
					],
					(object) [
						'name' => 'php-7.2.4',
					],
					(object) [
						'name' => 'php-7.3.0',
					],
					(object) [
						'name' => 'php-7.3.1',
					],
					(object) [
						'name' => 'php-7.3.2',
					],
					(object) [
						'name' => 'php-7.3.3',
					],
					(object) [
						'name' => 'php-7.3.4',
					],
				];
			}
		};

		$github = new class($githubRepositories) extends GitHub
		{
			private $mockedRepositories;

			public function __construct(Repositories $repositories)
			{
				parent::__construct();

				$this->mockedRepositories = $repositories;
			}

			public function __get($name)
			{
				if ($name === 'repositories')
				{
					return $this->mockedRepositories;
				}

				return parent::__get($name);
			}
		};

		$adapter = new MemoryAdapter;

		$filesystem = new Filesystem($adapter);

		$input  = new ArrayInput(
			[
				'command' => 'tags:php',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new FetchPhpTagsCommand($github, $filesystem);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('PHP versions updated.', $screenOutput);

		$versions = json_decode($filesystem->read('php.json'), true);

		$this->assertContains('7.1.5', $versions, 'The command should add the next patch release for the 7.1 branch to the allowed version list');
		$this->assertContains('7.2.5', $versions, 'The command should add the next patch release for the 7.2 branch to the allowed version list');
		$this->assertContains('7.3.5', $versions, 'The command should add the next patch release for the 7.3 branch to the allowed version list');
		$this->assertContains('7.4.0', $versions, 'The command should add the next minor release to the allowed version list');
	}

	/**
	 * @testdox The command fetches the release tags from the PHP repository with a paginated response
	 */
	public function testTheCommandFetchesTheReleaseTagsFromThePhpRepositoryWithAPaginatedResponse(): void
	{
		$response = new Response;

		$githubRepositories = new class extends Repositories
		{
			private $execution = 0;

			public function getApiResponse()
			{
				switch ($this->execution)
				{
					case 1:
						$response = new Response;
						$response = $response->withHeader('Link', '<https://api.github.com/repositories/1903522/tags?page=2>; rel="next", <https://api.github.com/repositories/1903522/tags?page=3>; rel="last"');

						return $response;

					default:
						return new Response;
				}
			}

			public function getTags($owner, $repo, $page = 0)
			{
				$this->execution++;

				switch ($this->execution)
				{
					case 1:
						return [
							(object) [
								'name' => 'php-7.1.0',
							],
							(object) [
								'name' => 'php-7.1.1',
							],
							(object) [
								'name' => 'php-7.1.2',
							],
							(object) [
								'name' => 'php-7.1.3',
							],
							(object) [
								'name' => 'php-7.1.4',
							],
						];

					case 2:
						return [
							(object) [
								'name' => 'php-7.2.0',
							],
							(object) [
								'name' => 'php-7.2.1',
							],
							(object) [
								'name' => 'php-7.2.2',
							],
							(object) [
								'name' => 'php-7.2.3',
							],
							(object) [
								'name' => 'php-7.2.4',
							],
						];

					case 3:
						return [
							(object) [
								'name' => 'php-7.3.0',
							],
							(object) [
								'name' => 'php-7.3.1',
							],
							(object) [
								'name' => 'php-7.3.2',
							],
							(object) [
								'name' => 'php-7.3.3',
							],
							(object) [
								'name' => 'php-7.3.4',
							],
						];
				}
			}
		};

		$github = new class($githubRepositories) extends GitHub
		{
			private $mockedRepositories;

			public function __construct(Repositories $repositories)
			{
				parent::__construct();

				$this->mockedRepositories = $repositories;
			}

			public function __get($name)
			{
				if ($name === 'repositories')
				{
					return $this->mockedRepositories;
				}

				return parent::__get($name);
			}
		};

		$adapter = new MemoryAdapter;

		$filesystem = new Filesystem($adapter);

		$input  = new ArrayInput(
			[
				'command' => 'tags:php',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new FetchPhpTagsCommand($github, $filesystem);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Fetching page 2 of 3 pages of tags.', $screenOutput);
		$this->assertStringContainsString('PHP versions updated.', $screenOutput);

		$versions = json_decode($filesystem->read('php.json'), true);

		$this->assertContains('7.1.5', $versions, 'The command should add the next patch release for the 7.1 branch to the allowed version list');
		$this->assertContains('7.2.5', $versions, 'The command should add the next patch release for the 7.2 branch to the allowed version list');
		$this->assertContains('7.3.5', $versions, 'The command should add the next patch release for the 7.3 branch to the allowed version list');
		$this->assertContains('7.4.0', $versions, 'The command should add the next minor release to the allowed version list');
	}
}
