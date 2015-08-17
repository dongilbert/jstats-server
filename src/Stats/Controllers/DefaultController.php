<?php

namespace Stats\Controllers;

use Joomla\Controller\AbstractController;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

/**
 * @method         \Stats\Application  getApplication()  Get the application object.
 * @property-read  \Stats\Application  $app              Application object
 */
abstract class DefaultController extends AbstractController implements ContainerAwareInterface
{
	use ContainerAwareTrait;
}
