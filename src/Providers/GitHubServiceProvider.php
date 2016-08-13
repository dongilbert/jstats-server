<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Github\Github as BaseGithub;
use Stats\GitHub\GitHub;

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
		$container->alias('github', BaseGithub::class)
			->alias(GitHub::class, BaseGithub::class)
			->share(
				BaseGithub::class,
				function (Container $container)
				{
					/** @var \Joomla\Registry\Registry $config */
					$config = $container->get('config');

					return new GitHub($config->extract('github'));
				},
				true
			);
	}
}
