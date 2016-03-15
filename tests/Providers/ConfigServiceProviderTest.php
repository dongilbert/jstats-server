<?php
namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Stats\Providers\ConfigServiceProvider;

/**
 * Test class for \Stats\Providers\ConfigServiceProvider
 */
class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The config service provider is registered to the DI container
	 *
	 * @covers  Stats\Providers\ConfigServiceProvider::__construct
	 * @covers  Stats\Providers\ConfigServiceProvider::register
	 */
	public function testTheConfigServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new ConfigServiceProvider(APPROOT . '/etc/config.dist.json'));

		$this->assertTrue($container->exists('config'));
	}

	/**
	 * @testdox The config service provider throws an Exception if an invalid file is given
	 *
	 * @covers  Stats\Providers\ConfigServiceProvider::__construct
	 * @expectedException  \RuntimeException
	 */
	public function testTheConfigServiceProviderThrowsAnExceptionIfAnInvalidFileIsGiven()
	{
		new ConfigServiceProvider('/bad/file/path.json');
	}
}
