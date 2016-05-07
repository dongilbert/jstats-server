<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Github\Github;

/**
 * GitHub service provider
 *
 * @since  1.0
 */
class GitHubServiceProvider implements ServiceProviderInterface
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
		$container->alias('github', Github::class)
			->share(
				Github::class,
				function (Container $container)
				{
					/** @var \Joomla\Registry\Registry $config */
					$config = $container->get('config');

					return new Github((array) $config->get('github'));
				},
				true
			);
	}
}
