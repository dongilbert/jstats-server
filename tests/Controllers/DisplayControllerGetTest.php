<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\Controllers;

use Joomla\Input\Input;
use Joomla\StatsServer\Controllers\DisplayControllerGet;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use Joomla\StatsServer\WebApplication;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\StatsServer\Controllers\DisplayControllerGet
 */
class DisplayControllerGetTest extends TestCase
{
	/**
	 * @testdox The controller is instantiated correctly
	 *
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerGet::__construct
	 */
	public function testTheControllerIsInstantiatedCorrectly()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$controller = new DisplayControllerGet($mockView);

		$this->assertAttributeSame($mockView, 'view', $controller);
	}

	/**
	 * @testdox The controller is executed correctly
	 *
	 * @covers  Joomla\StatsServer\Controllers\DisplayControllerGet::execute
	 */
	public function testTheControllerIsExecutedCorrectly()
	{
		$mockView = $this->getMockBuilder(StatsJsonView::class)
			->disableOriginalConstructor()
			->getMock();

		$mockView->expects($this->once())
			->method('render')
			->willReturn(json_encode(['error' => false]));

		$mockApp = $this->getMockBuilder(WebApplication::class)
			->disableOriginalConstructor()
			->getMock();

		$mockApp->expects($this->once())
			->method('get')
			->willReturn(false);

		$mockInput = $this->getMockBuilder(Input::class)
			->setConstructorArgs([[]])
			->enableProxyingToOriginalMethods()
			->setMethods(['get'])
			->getMock();

		$mockInput->expects($this->once())
			->method('get')
			->willReturn(null);

		$controller = (new DisplayControllerGet($mockView))
			->setApplication($mockApp)
			->setInput($mockInput);

		$this->assertTrue($controller->execute());
	}
}
