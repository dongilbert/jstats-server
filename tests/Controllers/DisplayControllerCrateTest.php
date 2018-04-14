<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Controllers;

use Joomla\StatsServer\Controllers\DisplayControllerCreate;
use Joomla\StatsServer\WebApplication;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Controllers\DisplayControllerCreate
 */
class DisplayControllerCreateTest extends TestCase
{
	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerCreate::execute
	 */
	public function testTheSubmitRouteOnlyAllowsPostRequests()
	{
		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = (new DisplayControllerCreate)
			->setApplication($mockApp);

		$this->assertTrue($controller->execute());
	}
}
