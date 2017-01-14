<?php

namespace Stats;

use Joomla\Controller\ControllerInterface;

/**
 * CLI Command Interface
 *
 * @since  1.0
 */
interface CommandInterface extends ControllerInterface
{
	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription() : string;

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle() : string;
}
