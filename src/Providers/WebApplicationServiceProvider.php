<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Providers;

use Joomla\Application\AbstractApplication;
use Joomla\Application\AbstractCliApplication;
use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Controller\ContainerControllerResolver;
use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Application\Web\WebClient;
use Joomla\Application\WebApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Joomla\Router\Router;
use Joomla\StatsServer\Controllers\DisplayControllerGet;
use Joomla\StatsServer\Controllers\SubmitControllerCreate;
use Joomla\StatsServer\Models\StatsModel;
use Joomla\StatsServer\Views\Stats\StatsJsonView;
use Psr\Log\LoggerInterface;
use TheIconic\Tracking\GoogleAnalytics\Analytics;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Application service provider
 */
class WebApplicationServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container): void
	{
		$container->alias(WebApplication::class, AbstractWebApplication::class)
			->share(AbstractWebApplication::class, [$this, 'getWebApplicationService'], true);

		/*
		 * Application Class Dependencies
		 */

		$container->share(Analytics::class, [$this, 'getAnalyticsService'], true);
		$container->share(Input::class, [$this, 'getInputService'], true);
		$container->share(Router::class, [$this, 'getRouterService'], true);

		$container->alias(ContainerControllerResolver::class, ControllerResolverInterface::class)
			->share(ControllerResolverInterface::class, [$this, 'getControllerResolverService'], true);

		$container->share(WebClient::class, [$this, 'getWebClientService'], true);

		/*
		 * MVC Layer
		 */

		// Controllers
		$container->share(DisplayControllerGet::class, [$this, 'getDisplayControllerGetService'], true);
		$container->share(SubmitControllerCreate::class, [$this, 'getSubmitControllerCreateService'], true);

		// Models
		$container->share(StatsModel::class, [$this, 'getStatsModelService'], true);

		// Views
		$container->share(StatsJsonView::class, [$this, 'getStatsJsonViewService'], true);
	}

	/**
	 * Get the Analytics class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Analytics
	 */
	public function getAnalyticsService(Container $container): Analytics
	{
		return new Analytics(true);
	}

	/**
	 * Get the controller resolver service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ControllerResolverInterface
	 */
	public function getControllerResolverService(Container $container): ControllerResolverInterface
	{
		return new ContainerControllerResolver($container);
	}

	/**
	 * Get the DisplayControllerGet class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DisplayControllerGet
	 */
	public function getDisplayControllerGetService(Container $container): DisplayControllerGet
	{
		$controller = new DisplayControllerGet(
			$container->get(StatsJsonView::class)
		);

		$controller->setApplication($container->get(AbstractApplication::class));
		$controller->setInput($container->get(Input::class));

		return $controller;
	}

	/**
	 * Get the Input\Cli class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Cli
	 */
	public function getInputCliService(Container $container): Cli
	{
		return new Cli;
	}

	/**
	 * Get the Input class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Input
	 */
	public function getInputService(Container $container): Input
	{
		return new Input($_REQUEST);
	}

	/**
	 * Get the router service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Router
	 */
	public function getRouterService(Container $container): Router
	{
		$router = new Router;

		$router->get(
			'/',
			DisplayControllerGet::class
		);

		$router->post(
			'/submit',
			SubmitControllerCreate::class
		);

		$router->get(
			'/:source',
			DisplayControllerGet::class,
			[
				'source' => '(' . implode('|', StatsModel::ALLOWED_SOURCES) . ')',
			]
		);

		return $router;
	}

	/**
	 * Get the StatsJsonView class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatsJsonView
	 */
	public function getStatsJsonViewService(Container $container): StatsJsonView
	{
		return new StatsJsonView(
			$container->get(StatsModel::class)
		);
	}

	/**
	 * Get the StatsModel class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  StatsModel
	 */
	public function getStatsModelService(Container $container): StatsModel
	{
		return new StatsModel(
			$container->get(DatabaseDriver::class)
		);
	}

	/**
	 * Get the SubmitControllerCreate class service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SubmitControllerCreate
	 */
	public function getSubmitControllerCreateService(Container $container): SubmitControllerCreate
	{
		$controller = new SubmitControllerCreate(
			$container->get(StatsModel::class)
		);

		$controller->setApplication($container->get(AbstractApplication::class));
		$controller->setInput($container->get(Input::class));

		return $controller;
	}

	/**
	 * Get the web application service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebApplication
	 */
	public function getWebApplicationService(Container $container): WebApplication
	{
		$application = new WebApplication(
			$container->get(ControllerResolverInterface::class),
			$container->get(Router::class),
			$container->get(Input::class),
			$container->get('config'),
			$container->get(WebClient::class),
			new JsonResponse([])
		);

		// Inject extra services
		$application->setDispatcher($container->get(DispatcherInterface::class));
		$application->setLogger($container->get(LoggerInterface::class));

		return $application;
	}

	/**
	 * Get the web client service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  WebClient
	 */
	public function getWebClientService(Container $container): WebClient
	{
		/** @var Input $input */
		$input          = $container->get(Input::class);
		$userAgent      = $input->server->getString('HTTP_USER_AGENT', '');
		$acceptEncoding = $input->server->getString('HTTP_ACCEPT_ENCODING', '');
		$acceptLanguage = $input->server->getString('HTTP_ACCEPT_LANGUAGE', '');

		return new WebClient($userAgent, $acceptEncoding, $acceptLanguage);
	}
}
