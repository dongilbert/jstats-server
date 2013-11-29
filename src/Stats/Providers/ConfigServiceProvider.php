<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
	/**
	 * Path to the config file.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Class constructor.
	 *
	 * @param string $path Path to the config file.
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register(Container $container)
	{
		$container->share("config", function () use ($container) {
			return include $this->path;
		});
	}
}
