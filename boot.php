<?php

const APPROOT = __DIR__;

$composerPath = APPROOT . '/vendor/autoload.php';

if (!file_exists($composerPath))
{
	throw new RuntimeException('Composer is not set up, please run "composer install".');
}

require APPROOT . '/vendor/autoload.php';
