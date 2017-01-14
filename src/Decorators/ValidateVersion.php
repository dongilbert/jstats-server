<?php

namespace Stats\Decorators;

/**
 * Decorator for objects which validate a version number.
 *
 * @since  1.0
 */
trait ValidateVersion
{
	/**
	 * Validates and filters the version number
	 *
	 * @param   string  $version  The version string to validate.
	 *
	 * @return  string|boolean  A validated version number on success or boolean false.
	 *
	 * @since   1.0
	 */
	protected function validateVersionNumber(string $version)
	{
		return preg_match('/\d+(?:\.\d+)+/', $version, $matches) ? $matches[0] : false;
	}
}
