<?php

namespace Stats\GitHub;

use Joomla\Github\Package as BasePackage;

/**
 * Extended GitHub API package class.
 *
 * @since  1.0
 */
abstract class Package extends BasePackage
{
	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  Package  GitHub API package object.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = __NAMESPACE__ . '\\' . $this->package . '\\' . ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client);
			}

			return $this->$name;
		}

		return parent::__get($name);
	}
}
