<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands\Tags;

use Joomla\StatsServer\Decorators\ValidateVersion;
use Joomla\StatsServer\GitHub\GitHub;

/**
 * Command for processing tags for the PHP project from GitHub
 */
class PhpCommand extends AbstractTagCommand
{
	use ValidateVersion;

	/**
	 * Constructor.
	 *
	 * @param   GitHub  $github  GitHub API object
	 */
	public function __construct(GitHub $github)
	{
		parent::__construct($github);

		$this->repoName  = 'php-src';
		$this->repoOwner = 'php';
	}

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 */
	public function execute()
	{
		$this->getApplication()->outputTitle('Fetching PHP Releases');

		$versions          = [];
		$supportedBranches = [
			'7.1' => '',
			'7.2' => '',
			'7.3' => '',
		];

		foreach ($this->getTags() as $tag)
		{
			// Replace 'php-' from the tag to get our version number; skip if the segment doesn't exist
			if (strpos($tag->name, 'php-') !== 0)
			{
				continue;
			}

			$version = substr($tag->name, 4);

			$version = $this->validateVersionNumber($version);

			// Only process if the tag name looks like a version number
			if ($version === false)
			{
				continue;
			}

			// We only track versions based on major.minor.patch so everything else is invalid
			$explodedVersion = explode('.', $version);

			if (count($explodedVersion) != 3)
			{
				continue;
			}

			// Joomla collects stats for the 3.x branch and later, the minimum PHP version for 3.0.0 was 5.3.1
			if (version_compare($version, '5.3.1', '<'))
			{
				continue;
			}

			// We have a valid version number, great news... add it to our array if it isn't already present
			if (!in_array($version, $versions))
			{
				$versions[] = $version;

				// If this version is higher than our branch's high version, replace it
				$branch = substr($version, 0, 3);

				if (isset($supportedBranches[$branch]) && version_compare($version, $supportedBranches[$branch], '>'))
				{
					$supportedBranches[$branch] = $version;
				}
			}
		}

		// For each supported branch, also add the next patch release
		foreach ($supportedBranches as $branch => $version)
		{
			$explodedVersion = explode('.', $version);

			$nextPatch = $explodedVersion[2] + 1;
			$versions[] = $explodedVersion[0] . '.' . $explodedVersion[1] . '.' . $nextPatch;
		}

		// Use $branch from the previous loop to allow the next minor version (PHP's master branch)
		$explodedVersion = explode('.', $branch);

		$nextMinor   = $explodedVersion[1] + 1;
		$nextRelease = $explodedVersion[0] . '.' . $nextMinor . '.0';

		if (!in_array($nextRelease, $versions, true))
		{
			$versions[] = $nextRelease;
		}

		// Store the version data now
		$path = APPROOT . '/versions/php.json';

		if (file_put_contents($path, json_encode($versions)) === false)
		{
			throw new \RuntimeException("Could not write version data to $path");
		}

		$this->getApplication()->out('<info>PHP versions successfully pulled.</info>');

		return true;
	}

	/**
	 * Get the command's description
	 *
	 * @return  string
	 */
	public function getDescription() : string
	{
		return 'Parses the release tags for the PHP GitHub repository.';
	}

	/**
	 * Get the command's title
	 *
	 * @return  string
	 */
	public function getTitle() : string
	{
		return 'Fetch PHP Releases';
	}
}
