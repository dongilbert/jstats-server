<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer;

use Joomla\Controller\ControllerInterface;

/**
 * CLI Command Interface
 */
interface CommandInterface extends ControllerInterface
{
	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription(): string;

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle(): string;
}
