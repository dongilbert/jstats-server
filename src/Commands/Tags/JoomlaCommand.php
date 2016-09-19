<?php

namespace Stats\Commands\Tags;

use Stats\Decorators\ValidateVersion;
use Stats\GitHub\GitHub;

/**
 * Command for processing tags for the Joomla! CMS from GitHub
 *
 * @since  1.0
 */
class JoomlaCommand extends AbstractTagCommand
{
	use ValidateVersion;

	/**
	 * Constructor.
	 *
	 * @param   GitHub  $github  GitHub API object
	 *
	 * @since   1.0
	 */
	public function __construct(GitHub $github)
	{
		parent::__construct($github);

		$this->repoName  = 'joomla-cms';
		$this->repoOwner = 'joomla';
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Fetching Joomla Releases');

		$versions    = [];
		$highVersion = '0.0.0';

		foreach ($this->getTags() as $tag)
		{
			$version = $this->validateVersionNumber($tag->name);

			// Only process if the tag name looks like a version number
			if ($version === false)
			{
				continue;
			}

			// Joomla only uses major.minor.patch so everything else is invalid
			$explodedVersion = explode('.', $version);

			if (count($explodedVersion) != 3)
			{
				continue;
			}

			// Version collection is valid for the 3.x series and later
			if (version_compare($version, '3.0.0', '<'))
			{
				continue;
			}

			// We have a valid version number, great news... add it to our array if it isn't already present
			if (!in_array($version, $versions))
			{
				$versions[] = $version;

				// If this version is higher than our high version, replace it
				if (version_compare($version, $highVersion, '>'))
				{
					$highVersion = $version;
				}
			}
		}

		// If the high version is not the default then let's add some (arbitrary) allowed versions based on the repo's dev structure
		if ($highVersion !== '0.0.0')
		{
			$explodedVersion = explode('.', $highVersion);

			// Allow the next patch release after this one
			$nextPatch = $explodedVersion[2] + 1;
			$versions[] = $explodedVersion[0] . '.' . $explodedVersion[1] . '.' . $nextPatch;

			// And allow the next minor release after this one
			$nextMinor = $explodedVersion[1] + 1;
			$versions[] = $explodedVersion[0] . '.' . $nextMinor . '.0';

			// And allow the next major release after this one
			// TODO - Remove this once there is a 4.0 tag
			$nextMajor = $explodedVersion[0] + 1;
			$versions[] = $nextMajor . '.0.0';
		}

		// Store the version data now
		$path = APPROOT . '/versions/joomla.json';

		if (file_put_contents($path, json_encode($versions)) === false)
		{
			throw new \RuntimeException("Could not write version data to $path");
		}

		$this->getApplication()->out('<info>Joomla! versions successfully pulled.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDescription()
	{
		return 'Parses the release tags for the Joomla! CMS GitHub repository.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getTitle()
	{
		return 'Fetch Joomla! Releases';
	}
}
