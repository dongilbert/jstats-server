<?php
namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Joomla\Github\Github;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;
use Stats\Providers\GitHubServiceProvider;

/**
 * Test class for \Stats\Providers\GitHubServiceProvider
 */
class GitHubServiceProviderTest extends TestCase
{
	/**
	 * @testdox The GitHub service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\GitHubServiceProvider::register
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
	 * @covers  Stats\Providers\GitHubServiceProvider::getGithubService
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
