<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

// Application constants
define('APPROOT', dirname(__DIR__));

// Ensure we've initialized Composer
if (!file_exists(APPROOT . '/vendor/autoload.php'))
{
    fwrite(STDOUT, "\nComposer is not set up properly, please run `composer install`\n");

	exit(1);
}

require APPROOT . '/vendor/autoload.php';
