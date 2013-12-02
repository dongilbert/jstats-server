<?php
/**
 * Part of the Joomla Tracker View Package
 *
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Stats\Renderer\Extensions;

use Stats\Application;

/**
 * Twig extension class
 *
 * @since  1.0
 */
class StatsExtension extends \Twig_Extension
{
	/**
	 * @var Application
	 */
	protected $app;

	/**
	 * Returns the name of the extension.
	 *
	 * @return  string  The extension name.
	 *
	 * @since   1.0
	 */
	public function getName()
	{
		return 'stats';
	}

	public function setApplication(Application $app)
	{
		$this->app = $app;

		return $this;
	}


	/**
	 * Returns a list of global variables to add to the existing list.
	 *
	 * @return  array  An array of global variables.
	 *
	 * @since   1.0
	 */
	public function getGlobals()
	{
		return array(
			'uri' => $this->app->get('uri'),
			'uri.base.path' => $this->app->get('uri.base.path')
		);
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return  array  An array of functions.
	 *
	 * @since   1.0
	 */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('sprintf', 'sprintf')
		];
	}

	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return  array  An array of filters
	 *
	 * @since   1.0
	 */
	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter('basename', 'basename'),
			new \Twig_SimpleFilter('get_class', 'get_class'),
			new \Twig_SimpleFilter('json_decode', 'json_decode'),
			new \Twig_SimpleFilter('_', 'echo'),
		];
	}
}
