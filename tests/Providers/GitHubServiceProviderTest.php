<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Providers;

use Joomla\DI\Container;
use Joomla\Github\Github;
use Joomla\Registry\Registry;
use Joomla\StatsServer\Providers\GitHubServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Providers\GitHubServiceProvider
 */
class GitHubServiceProviderTest extends TestCase
{
	/**
	 * @testdox The GitHub service provider is registered to the DI container
	 *
	 * @covers  Joomla\StatsServer\Providers\GitHubServiceProvider::register
	 */
	public function testTheGitHubServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new GitHubServiceProvider);

		$this->assertTrue($container->exists(Github::class));
	}

	/**
	 * @testdox The GitHub service is created
	 *
	 * @covers  Joomla\StatsServer\Providers\GitHubServiceProvider::getGithubService
	 */
	public function testTheGitHubServiceIsCreated()
	{
		$mockConfig = $this->createMock(Registry::class);
		$mockConfig->expects($this->once())
			->method('extract')
			->with('github')
			->willReturn($this->createMock(Registry::class));

		$mockContainer = $this->createMock(Container::class);
		$mockContainer->expects($this->once())
			->method('get')
			->with('config')
			->willReturn($mockConfig);

		$this->assertInstanceOf(Github::class, (new GitHubServiceProvider)->getGithubService($mockContainer));
	}
}
