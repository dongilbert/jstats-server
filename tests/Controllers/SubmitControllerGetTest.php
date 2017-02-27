<?php

namespace Stats\Tests\Controllers;

use PHPUnit\Framework\TestCase;
use Stats\Controllers\SubmitControllerGet;
use Stats\WebApplication;

/**
 * Test class for \Stats\Controllers\SubmitControllerGet
 */
class SubmitControllerGetTest extends TestCase
{
	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Stats\Controllers\SubmitControllerGet::execute
	 */
	public function testTheSubmitRouteOnlyAllowsPostRequests()
	{
		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = (new SubmitControllerGet)
			->setApplication($mockApp);

		$this->assertTrue($controller->execute());
	}
}
