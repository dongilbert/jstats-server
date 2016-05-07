<?php

namespace Stats\GitHub;

use Joomla\Github\Github as JGitHub;

/**
 * Extended GitHub API object.
 *
 * @property-read  Package\Repositories   $repositories   GitHub API object for the repositories package.
 *
 * @since  1.0
 */
class GitHub extends JGitHub
{
	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  \Joomla\Github\AbstractGithubObject  GitHub API object (gists, issues, pulls, etc).
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException If $name is not a valid sub class.
	 */
	public function __get($name)
	{
		$class = __NAMESPACE__ . '\\Package\\' . ucfirst($name);

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
