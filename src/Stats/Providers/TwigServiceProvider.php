<?php

namespace Stats\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Renderer\TwigRenderer;
use Stats\Renderer\Extensions\StatsExtension;

/**
 * Class TwigServiceProvider
 *
 * @package Stats\Providers
 */
class TwigServiceProvider implements ServiceProviderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function register(Container $container)
	{
		$container->alias('renderer', 'Joomla\\Renderer\\RendererInterface')
			->set('Joomla\\Renderer\\RendererInterface',
				function (Container $container) {
					/* @type  \Joomla\Registry\Registry  $config */
					$config = $container->get('config');

					// Setup the path
					$templateConfig = $config->get('template');
					$templateConfig->path = JPATH_TEMPLATES;

					// Instantiate the renderer object
					$renderer = new TwigRenderer($config->get('template'));

					// Add our Twig extension
					$renderer->getRenderer()->addExtension(new StatsExtension($container->get('app')));

					// Add the debug extension if enabled
					if ($config->get('template.debug'))
					{
						$renderer->getRenderer()->addExtension(new \Twig_Extension_Debug);
					}

					// Set the Lexer object
					$renderer->getRenderer()->setLexer(
						new \Twig_Lexer($renderer->getRenderer(), ['delimiters' => [
							'tag_comment'  => ['{#', '#}'],
							'tag_block'    => ['{%', '%}'],
							'tag_variable' => ['{{', '}}']
						]])
					);

					return $renderer;
				},
				true,
				true
			);
	}
}
