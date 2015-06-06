<?php
/**
 * Part of the Joomla Tracker View Package
 *
 * @copyright  Copyright (C) 2012 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Stats\Views;

use Joomla\Model\ModelInterface;
use Joomla\View\AbstractView;
use Joomla\View\Renderer\RendererInterface;
use Stats\Renderer\Extensions\StatsExtension;

/**
 * Abstract HTML view class for the Tracker application
 *
 * @since  1.0
 */
abstract class AbstractHtmlView extends AbstractView
{
	/**
	 * The view layout.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $layout = 'index';

	/**
	 * The view template engine.
	 *
	 * @var    RendererInterface
	 * @since  1.0
	 */
	protected $renderer = null;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   ModelInterface  $model           The model object.
	 * @param   string|array    $templatesPaths  The templates paths.
	 *
	 * @throws  \RuntimeException
	 * @since   1.0
	 */
	public function __construct(ModelInterface $model, $templatesPaths = '')
	{
		parent::__construct($model);

		$renderer = 'TwigRenderer';

		$className = 'Joomla\\Renderer\\' . ucfirst($renderer);

		// Check if the specified renderer exists in the application
		if (false == class_exists($className))
		{
			throw new \RuntimeException(sprintf('Invalid renderer: %s', $renderer));
		}

		$config = array(
			'extension'  => '.twig',
			'twig_cache_dir'     => 'cache/twig/',
			'delimiters'         => array(
				'tag_comment'    => array('{#', '#}'),
				'tag_block'      => array('{%', '%}'),
				'tag_variable'   => array('{{', '}}')
			)
		);

		switch ($renderer)
		{
			case 'TwigRenderer':
				$config['path'] = JPATH_TEMPLATES;

				break;

			default:
				throw new \RuntimeException('Unsupported renderer: ' . $renderer);
				break;
		}

		// Load the renderer.
		$this->renderer = new $className($config);

		// Register additional paths.
		if (!empty($templatesPaths))
		{
			foreach($templatesPaths as $templatePath)
			{
				$this->getRenderer()->addFolder($templatePath);
			}
		}
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     ViewInterface::escape()
	 * @since   1.0
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get the view layout.
	 *
	 * @return  string  The layout name.
	 *
	 * @since   1.0
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the renderer object.
	 *
	 * @return  RendererInterface  The renderer object.
	 *
	 * @since   1.0
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function render()
	{
		return $this->getRenderer()->render($this->layout);
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  $this  Method supports chaining
	 *
	 * @since   1.0
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}
}
