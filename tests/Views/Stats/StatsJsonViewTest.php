<?php
namespace Stats\Tests\Views\Stats;

use Stats\Views\Stats\StatsJsonView;

/**
 * Test class for \Stats\Views\Stats\StatsJsonView
 */
class StatsJsonViewTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @testdox The authorized raw flag is set to the view
	 *
	 * @covers  Stats\Views\Stats\StatsJsonView::isAuthorizedRaw
	 */
	public function testTheAuthorizedRawFlagIsSetToTheView()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$authorizedRaw = true;

		$view = new StatsJsonView($mockModel);
		$view->isAuthorizedRaw($authorizedRaw);

		$this->assertAttributeSame($authorizedRaw, 'authorizedRaw', $view);
	}

	/**
	 * @testdox The statistics data is returned
	 *
	 * @covers  Stats\Views\Stats\StatsJsonView::render
	 */
	public function testTheStatisticsDataIsReturned()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('getItems')
			->willReturn([
				(object) [
					'unique_id'   => '1a',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'mysql',
					'db_version'  => '5.6.25',
					'server_os'   => 'Darwin 14.1.0'
				],
				(object) [
					'unique_id'   => '2b',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'postgresql',
					'db_version'  => '9.4.0',
					'server_os'   => 'Darwin 14.1.0'
				],
			]);

		$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

		$returnData = [
			'data' => [
				'php_version' => [$phpVersion => 100],
				'db_type'     => ['mysql' => 50, 'postgresql' => 50],
				'db_version'  => ['5.6' => 50, '9.4' => 50],
				'cms_version' => ['3.5.0' => 100],
				'server_os'   => ['Darwin' => 100],
				'total'       => 2
			]
		];

		$view = new StatsJsonView($mockModel);

		$this->assertSame($returnData, json_decode($view->render(), true));
	}

	/**
	 * @testdox The raw statistics data is returned
	 *
	 * @covers  Stats\Views\Stats\StatsJsonView::render
	 */
	public function testTheRawStatisticsDataIsReturned()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('getItems')
			->willReturn([
				(object) [
					'unique_id'   => '1a',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'mysql',
					'db_version'  => '5.6.25',
					'server_os'   => 'Darwin 14.1.0'
				],
				(object) [
					'unique_id'   => '2b',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'postgresql',
					'db_version'  => '9.4.0',
					'server_os'   => 'Darwin 14.1.0'
				],
			]);

		$returnData = [
			'data' => [
				'php_version' => [
					[
						'name'  => PHP_VERSION,
						'count' => 2
					]
				],
				'db_type'     => [
					[
						'name'  => 'mysql',
						'count' => 1
					],
					[
						'name'  => 'postgresql',
						'count' => 1
					]

				],
				'db_version'  => [
					[
						'name'  => '5.6.25',
						'count' => 1
					],
					[
						'name'  => '9.4.0',
						'count' => 1
					]
				],
				'cms_version' => [
					[
						'name'  => '3.5.0',
						'count' => 2
					]
				],
				'server_os'   => [
					[
						'name'  => 'Darwin 14.1.0',
						'count' => 2
					]
				],
				'total'       => 2
			]
		];

		$view = new StatsJsonView($mockModel);
		$view->isAuthorizedRaw(true);

		$this->assertSame($returnData, json_decode($view->render(), true));
	}

	/**
	 * @testdox The statistics data for a single source is returned
	 *
	 * @covers  Stats\Views\Stats\StatsJsonView::render
	 * @uses    Stats\Views\Stats\StatsJsonView::setSource
	 */
	public function testTheStatisticsDataForASingleSourceIsReturned()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('getItems')
			->willReturn([
				(object) [
					'unique_id'   => '1a',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'mysql',
					'db_version'  => '5.6.25',
					'server_os'   => 'Darwin 14.1.0'
				],
				(object) [
					'unique_id'   => '2b',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'postgresql',
					'db_version'  => '9.4.0',
					'server_os'   => 'Darwin 14.1.0'
				],
			]);

		$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

		$returnData = [
			'data' => [
				'php_version' => [$phpVersion => 100],
				'total'       => 2
			]
		];

		$view = new StatsJsonView($mockModel);
		$view->setSource('php_version');

		$this->assertSame($returnData, json_decode($view->render(), true));
	}

	/**
	 * @testdox An Exception is thrown if the requested data source does not exist
	 *
	 * @covers  Stats\Views\Stats\StatsJsonView::render
	 * @uses    Stats\Views\Stats\StatsJsonView::setSource
	 * @expectedException  \InvalidArgumentException
	 */
	public function testAnExceptionIsThrownIfTheRequestedDataSourceDoesNotExist()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$mockModel->expects($this->once())
			->method('getItems')
			->willReturn([
				(object) [
					'unique_id'   => '1a',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'mysql',
					'db_version'  => '5.6.25',
					'server_os'   => 'Darwin 14.1.0'
				],
				(object) [
					'unique_id'   => '2b',
					'php_version' => PHP_VERSION,
					'cms_version' => '3.5.0',
					'db_type'     => 'postgresql',
					'db_version'  => '9.4.0',
					'server_os'   => 'Darwin 14.1.0'
				],
			]);

		$view = new StatsJsonView($mockModel);
		$view->setSource('noway');
		$view->render();
	}

	/**
	 * @testdox The data source is set to the view
	 *
	 * @covers  Stats\Views\Stats\StatsJsonView::setSource
	 */
	public function testTheDataSourceIsSetToTheView()
	{
		$mockModel = $this->getMockBuilder('Stats\Models\StatsModel')
			->disableOriginalConstructor()
			->getMock();

		$source = 'php_version';

		$view = new StatsJsonView($mockModel);
		$view->setSource('php_version');

		$this->assertAttributeSame($source, 'source', $view);
	}
}
