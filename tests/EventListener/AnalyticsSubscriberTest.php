<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Tests\EventListener;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\ApplicationEvents;
use Joomla\Application\Event\ApplicationEvent;
use Joomla\Input\Input;
use Joomla\StatsServer\EventListener\AnalyticsSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use TheIconic\Tracking\GoogleAnalytics\Analytics;
use TheIconic\Tracking\GoogleAnalytics\NullAnalyticsResponse;

/**
 * Test class for \Joomla\StatsServer\EventListener\AnalyticsSubscriber
 */
class AnalyticsSubscriberTest extends TestCase
{
	/**
	 * @testdox Analytics are recorded for GET requests to the live site
	 */
	public function testAnalyticsAreRecordedForGetRequestsToTheLiveSite(): void
	{
		$mockInput = new class extends Input
		{
			private $mockServer;

			public function __get($name)
			{
				if ($name === 'server')
				{
					return $this->getMockServer();
				}

				return parent::__get($name);
			}

			private function getMockServer(): Input
			{
				if ($this->mockServer === null)
				{
					$this->mockServer = new static(
						[
							'HTTP_HOST'       => 'developer.joomla.org',
							'HTTP_USER_AGENT' => 'JoomlaStatsTest/1.0',
							'REMOTE_ADDR'     => '127.0.0.1',
							'REQUEST_METHOD'  => 'GET',
						]
					);
				}

				return $this->mockServer;
			}
		};

		$application = $this->createMock(AbstractWebApplication::class);

		$application->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn($mockInput);

		$application->expects($this->once())
			->method('get')
			->with('uri.base.path')
			->willReturn('/');

		$analytics = new class extends Analytics
		{
			private $didSend = false;

			public function isSent(): bool
			{
				return $this->didSend === true;
			}

			public function sendPageview()
			{
				$this->didSend = true;

				return new NullAnalyticsResponse;
			}
		};

		$event = new ApplicationEvent(ApplicationEvents::BEFORE_EXECUTE, $application);

		$logger = new TestLogger;

		$subscriber = new AnalyticsSubscriber($analytics);
		$subscriber->setLogger($logger);
		$subscriber->onBeforeExecute($event);

		$this->assertTrue($analytics->isSent());
	}

	/**
	 * @testdox Analytics are not recorded for POST requests to the live site
	 */
	public function testAnalyticsAreNotRecordedForPostRequestsToTheLiveSite(): void
	{
		$mockInput = new class extends Input
		{
			private $mockServer;

			public function __get($name)
			{
				if ($name === 'server')
				{
					return $this->getMockServer();
				}

				return parent::__get($name);
			}

			private function getMockServer(): Input
			{
				if ($this->mockServer === null)
				{
					$this->mockServer = new static(
						[
							'HTTP_HOST'       => 'developer.joomla.org',
							'HTTP_USER_AGENT' => 'JoomlaStatsTest/1.0',
							'REMOTE_ADDR'     => '127.0.0.1',
							'REQUEST_METHOD'  => 'POST',
						]
					);
				}

				return $this->mockServer;
			}
		};

		$application = $this->createMock(AbstractWebApplication::class);

		$application->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn($mockInput);

		$application->expects($this->never())
			->method('get');

		$analytics = new class extends Analytics
		{
			private $didSend = false;

			public function isSent(): bool
			{
				return $this->didSend === true;
			}

			public function sendPageview()
			{
				$this->didSend = true;

				return new NullAnalyticsResponse;
			}
		};

		$event = new ApplicationEvent(ApplicationEvents::BEFORE_EXECUTE, $application);

		$logger = new TestLogger;

		$subscriber = new AnalyticsSubscriber($analytics);
		$subscriber->setLogger($logger);
		$subscriber->onBeforeExecute($event);

		$this->assertFalse($analytics->isSent());
	}

	/**
	 * @testdox An error while sending analytics is handled
	 */
	public function testAnErrorWhileSendingAnalyticsIsHandled(): void
	{
		$mockInput = new class extends Input
		{
			private $mockServer;

			public function __get($name)
			{
				if ($name === 'server')
				{
					return $this->getMockServer();
				}

				return parent::__get($name);
			}

			private function getMockServer(): Input
			{
				if ($this->mockServer === null)
				{
					$this->mockServer = new static(
						[
							'HTTP_HOST'       => 'developer.joomla.org',
							'HTTP_USER_AGENT' => 'JoomlaStatsTest/1.0',
							'REMOTE_ADDR'     => '127.0.0.1',
							'REQUEST_METHOD'  => 'GET',
						]
					);
				}

				return $this->mockServer;
			}
		};

		$application = $this->createMock(AbstractWebApplication::class);

		$application->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn($mockInput);

		$application->expects($this->once())
			->method('get')
			->with('uri.base.path')
			->willReturn('/');

		$analytics = new class extends Analytics
		{
			private $didSend = false;

			public function isSent(): bool
			{
				return $this->didSend === true;
			}

			public function sendPageview(): void
			{
				throw new \Exception('Testing error handling');
			}
		};

		$event = new ApplicationEvent(ApplicationEvents::BEFORE_EXECUTE, $application);

		$logger = new TestLogger;

		$subscriber = new AnalyticsSubscriber($analytics);
		$subscriber->setLogger($logger);
		$subscriber->onBeforeExecute($event);

		$this->assertTrue($logger->hasErrorThatContains('Error sending analytics data.'));
	}
}
