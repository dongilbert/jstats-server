<?php
namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Joomla\Github\Github;
use Stats\Providers\GitHubServiceProvider;

/**
 * Test class for \Stats\Providers\GitHubServiceProvider
 */
class GitHubServiceProviderTest extends \PHPUnit_Framework_TestCase
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
}
