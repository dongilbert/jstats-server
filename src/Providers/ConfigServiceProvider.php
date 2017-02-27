<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Providers;

use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\Registry\Registry;

/**
 * Configuration service provider
 *
 * @since  1.0
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
	/**
	 * Configuration instance
	 *
	 * @var    Registry
	 * @since  1.0
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @param   string  $file  Path to the config file.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct(string $file)
	{
		// Verify the configuration exists and is readable.
		if (!is_readable($file))
		{
			throw new \RuntimeException('Configuration file does not exist or is unreadable.');
		}

		$this->config = (new Registry)->loadFile($file);
	}

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
		$container->share('config', [$this, 'getConfigService'], true);
	}

	/**
	 * Get the `config` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Registry
	 *
	 * @since   1.0
	 */
	public function getConfigService(Container $container) : Registry
	{
		return $this->config;
	}
}
