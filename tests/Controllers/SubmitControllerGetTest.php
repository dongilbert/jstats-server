<?php
namespace Stats\Tests\Controllers;

use Stats\Controllers\SubmitControllerGet;

/**
 * Test class for \Stats\Controllers\SubmitControllerGet
 */
class SubmitControllerGetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The /submit route only allows POST requests
	 *
	 * @covers  Stats\Controllers\SubmitControllerGet::execute
	 * @expectedException  \RuntimeException
	 */
	public function testTheSubmitRouteOnlyAllowsPostRequests()
	{
		$controller = (new SubmitControllerGet)
			->execute();
	}
}
