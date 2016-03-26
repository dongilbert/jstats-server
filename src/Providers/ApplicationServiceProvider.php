<?php

namespace Stats\Providers;

use Doctrine\Common\Cache\Cache;
use Joomla\Application as JoomlaApplication;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use Stats\Application;
use Stats\Controllers\DisplayControllerGet;
use Stats\Controllers\SubmitControllerCreate;
use Stats\Controllers\SubmitControllerGet;
use Stats\Models\StatsModel;
use Stats\Router;
use Stats\Views\Stats\StatsJsonView;

/**
 * Application service provider
 *
 * @since  1.0
 */
class ApplicationServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		$container->alias(Application::class, JoomlaApplication\AbstractApplication::class)
			->alias(JoomlaApplication\AbstractWebApplication::class, JoomlaApplication\AbstractApplication::class)
			->share(
				JoomlaApplication\AbstractApplication::class,
				function (Container $container)
				{
					$application = new Application($container->get(Input::class), $container->get('config'));

					// Inject extra services
					$application->setLogger($container->get('monolog.logger.application'));
					$application->setRouter($container->get(Router::class));

					return $application;
				},
				true
			);

		$container->share(
			Input::class,
			function ()
			{
				return new Input($_REQUEST);
			},
			true
		);

		$container->share(
			Router::class,
			function (Container $container)
			{
				$router = (new Router($container->get(Input::class)))
					->setContainer($container)
					->setControllerPrefix('Stats\\Controllers\\')
					->setDefaultController('DisplayController')
					->addMap('/submit', 'SubmitController')
					->addMap('/:source', 'DisplayController');

				return $router;
			},
			true
		);

		$container->share(
			DisplayControllerGet::class,
			function (Container $container)
			{
				$controller = new DisplayControllerGet(
					$container->get(StatsJsonView::class),
					$container->get(Cache::class)
				);

				$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$controller->setInput($container->get(Input::class));

				return $controller;
			},
			true
		);

		$container->share(
			SubmitControllerCreate::class,
			function (Container $container)
			{
				$controller = new SubmitControllerCreate(
					$container->get(StatsModel::class)
				);

				$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$controller->setInput($container->get(Input::class));

				return $controller;
			},
			true
		);

		$container->share(
			SubmitControllerGet::class,
			function (Container $container)
			{
				$controller = new SubmitControllerGet;

				$controller->setApplication($container->get(JoomlaApplication\AbstractApplication::class));
				$controller->setInput($container->get(Input::class));

				return $controller;
			},
			true
		);

		$container->share(
			StatsModel::class,
			function (Container $container)
			{
				return new StatsModel(
					$container->get(DatabaseDriver::class)
				);
			},
			true
		);

		$container->share(
			StatsJsonView::class,
			function (Container $container)
			{
				return new StatsJsonView(
					$container->get(StatsModel::class)
				);
			},
			true
		);
	}
}
