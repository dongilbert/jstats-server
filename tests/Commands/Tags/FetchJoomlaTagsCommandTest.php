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
use Joomla\StatsServer\Commands\Tags\FetchJoomlaTagsCommand;
use Joomla\StatsServer\GitHub\GitHub;
use Joomla\StatsServer\GitHub\Package\Repositories;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test class for \Joomla\StatsServer\Commands\Tags\FetchJoomlaTagsCommand
 */
class FetchJoomlaTagsCommandTest extends TestCase
{
	/**
	 * @testdox The command fetches the release tags from the Joomla! repository with no extra pages
	 */
	public function testTheCommandFetchesTheReleaseTagsFromTheJoomlaRepositoryWithNoExtraPages(): void
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
						'name' => '3.9.0',
					],
					(object) [
						'name' => '3.9.1',
					],
					(object) [
						'name' => '3.9.2',
					],
					(object) [
						'name' => '3.9.3',
					],
					(object) [
						'name' => '3.9.4',
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
				'command' => 'tags:joomla',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new FetchJoomlaTagsCommand($github, $filesystem);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Joomla! versions updated.', $screenOutput);

		$versions = json_decode($filesystem->read('joomla.json'), true);

		$this->assertContains('3.9.5', $versions, 'The command should add the next patch release to the allowed version list');
		$this->assertContains('3.10.0', $versions, 'The command should add the next minor release to the allowed version list');
	}

	/**
	 * @testdox The command fetches the release tags from the Joomla! repository with a paginated response
	 */
	public function testTheCommandFetchesTheReleaseTagsFromTheJoomlaRepositoryWithAPaginatedResponse(): void
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
						$response = $response->withHeader('Link', '<https://api.github.com/repositories/2464908/tags?page=2>; rel="next", <https://api.github.com/repositories/2464908/tags?page=3>; rel="last"');

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
								'name' => '3.7.0',
							],
							(object) [
								'name' => '3.7.1',
							],
							(object) [
								'name' => '3.7.2',
							],
							(object) [
								'name' => '3.7.3',
							],
							(object) [
								'name' => '3.7.4',
							],
							(object) [
								'name' => '3.7.5',
							],
						];

					case 2:
						return [
							(object) [
								'name' => '3.8.0',
							],
							(object) [
								'name' => '3.8.1',
							],
							(object) [
								'name' => '3.8.2',
							],
							(object) [
								'name' => '3.8.3',
							],
							(object) [
								'name' => '3.8.4',
							],
							(object) [
								'name' => '3.8.5',
							],
							(object) [
								'name' => '3.8.6',
							],
							(object) [
								'name' => '3.8.7',
							],
							(object) [
								'name' => '3.8.8',
							],
							(object) [
								'name' => '3.8.9',
							],
							(object) [
								'name' => '3.8.10',
							],
							(object) [
								'name' => '3.8.11',
							],
							(object) [
								'name' => '3.8.12',
							],
							(object) [
								'name' => '3.8.13',
							],
						];

					case 3:
						return [
							(object) [
								'name' => '3.9.0',
							],
							(object) [
								'name' => '3.9.1',
							],
							(object) [
								'name' => '3.9.2',
							],
							(object) [
								'name' => '3.9.3',
							],
							(object) [
								'name' => '3.9.4',
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
				'command' => 'tags:joomla',
			]
		);
		$output = new BufferedOutput;

		$application = new Application($input, $output);

		$command = new FetchJoomlaTagsCommand($github, $filesystem);
		$command->setApplication($application);

		$this->assertSame(0, $command->execute($input, $output));

		$screenOutput = $output->fetch();

		$this->assertStringContainsString('Fetching page 2 of 3 pages of tags.', $screenOutput);
		$this->assertStringContainsString('Joomla! versions updated.', $screenOutput);

		$versions = json_decode($filesystem->read('joomla.json'), true);

		$this->assertContains('3.9.5', $versions, 'The command should add the next patch release to the allowed version list');
		$this->assertContains('3.10.0', $versions, 'The command should add the next minor release to the allowed version list');
	}
}
