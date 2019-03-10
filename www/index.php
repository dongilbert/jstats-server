<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

// Application constants
\define('APPROOT', \dirname(__DIR__));

// Ensure we've initialized Composer
if (!file_exists(APPROOT . '/vendor/autoload.php'))
{
	header('HTTP/1.1 500 Internal Server Error', null, 500);
	echo '<html><head><title>Server Error</title></head><body><h1>Composer Not Installed</h1><p>Composer is not set up properly, please run "composer install".</p></body></html>';

	exit(500);
}

require APPROOT . '/vendor/autoload.php';

try
{
	(new \Joomla\StatsServer\Kernel\WebKernel)->run();
}
catch (\Throwable $throwable)
{
	error_log($throwable);

	if (!headers_sent())
	{
		header('HTTP/1.1 500 Internal Server Error', null, 500);
		header('Content-Type: text/html; charset=utf-8');
	}

	echo '<html><head><title>Application Error</title></head><body><h1>Application Error</h1><p>An error occurred while executing the application: ' . $throwable->getMessage() . '</p></body></html>';

	exit(1);
}
