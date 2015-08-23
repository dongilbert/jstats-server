<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use Stats\Application;
use Stats\Controllers\DisplayControllerGet;
use Stats\Controllers\SubmitControllerPost;
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
		$container->alias('Stats\\Application', 'Joomla\\Application\\AbstractApplication')
			->alias('Joomla\\Application\\AbstractWebApplication', 'Joomla\\Application\\AbstractApplication')
			->share(
				'Joomla\\Application\\AbstractApplication',
				function (Container $container)
				{
					$application = new Application($container->get('Joomla\\Input\\Input'), $container->get('config'));

					// Inject extra services
					$application->setRouter($container->get('Stats\\Router'));

					return $application;
				},
				true
			);

		$container->share(
			'Joomla\\Input\\Input',
			function ()
			{
				return new Input($_REQUEST);
			},
			true
		);

		$container->share(
			'Stats\\Router',
			function (Container $container)
			{
				$router = (new Router($container->get('Joomla\\Input\\Input')))
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
			'Stats\\Controllers\\DisplayControllerGet',
			function (Container $container)
			{
				$controller = new DisplayControllerGet(
					$container->get('Stats\\Views\\Stats\\StatsJsonView')
				);

				$controller->setApplication($container->get('Joomla\\Application\\AbstractApplication'));
				$controller->setInput($container->get('Joomla\\Input\\Input'));

				return $controller;
			},
			true
		);

		$container->share(
			'Stats\\Controllers\\SubmitControllerPost',
			function (Container $container)
			{
				$controller = new SubmitControllerPost(
					$container->get('Stats\\Models\\StatsModel')
				);

				$controller->setApplication($container->get('Joomla\\Application\\AbstractApplication'));
				$controller->setInput($container->get('Joomla\\Input\\Input'));

				return $controller;
			},
			true
		);

		$container->share(
			'Stats\\Models\\StatsModel',
			function (Container $container)
			{
				return new StatsModel(
					$container->get('Joomla\\Database\\DatabaseDriver')
				);
			},
			true
		);

		$container->share(
			'Stats\\Views\\Stats\\StatsJsonView',
			function (Container $container)
			{
				return new StatsJsonView(
					$container->get('Stats\\Models\\StatsModel')
				);
			},
			true
		);
	}
}
