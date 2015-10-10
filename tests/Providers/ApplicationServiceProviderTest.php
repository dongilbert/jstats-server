<?php
namespace Stats\Tests\Providers;

use Joomla\DI\Container;
use Stats\Providers\ApplicationServiceProvider;

/**
 * Test class for \Stats\Providers\ApplicationServiceProvider
 */
class ApplicationServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox The application service provider is registered to the DI container
     *
     * @covers Stats\Providers\ApplicationServiceProvider::register
     */
	public function testTheApplicationServiceProviderIsRegisteredToTheContainer()
	{
		$container = new Container;
		$container->registerServiceProvider(new ApplicationServiceProvider);

		$this->assertTrue($container->exists('Stats\\Application'));
	}
}
