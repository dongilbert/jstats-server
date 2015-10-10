<?php
namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Stats\Providers\DatabaseServiceProvider;

/**
 * Test class for \Stats\Providers\DatabaseServiceProvider
 */
class DatabaseServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The database service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\DatabaseServiceProvider::register
	 */
	public function testTheDatabaseServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new DatabaseServiceProvider);

		$this->assertTrue($container->exists('Joomla\\Database\\DatabaseDriver'));
	}
}
