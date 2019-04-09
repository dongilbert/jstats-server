<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Commands\Tags;

use Joomla\Console\Command\AbstractCommand;
use Joomla\StatsServer\GitHub\GitHub;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Abstract command for processing tags from GitHub
 */
abstract class AbstractTagCommand extends AbstractCommand
{
	/**
	 * GitHub API object.
	 *
	 * @var  GitHub
	 */
	protected $github;

	/**
	 * Filesystem adapter for the snapshots space.
	 *
	 * @var  Filesystem
	 */
	protected $filesystem;

	/**
	 * The GitHub repository to query.
	 *
	 * @var  string
	 */
	protected $repoName;

	/**
	 * The owner of the GitHub repository to query.
	 *
	 * @var  string
	 */
	protected $repoOwner;

	/**
	 * Constructor.
	 *
	 * @param   GitHub      $github      GitHub API object.
	 * @param   Filesystem  $filesystem  Filesystem adapter for the versions space.
	 */
	public function __construct(GitHub $github, Filesystem $filesystem)
	{
		$this->github     = $github;
		$this->filesystem = $filesystem;

		parent::__construct();
	}

	/**
	 * Get the tags for a repository
	 *
	 * @param   SymfonyStyle  $symfonyStyle  The I/O helper
	 *
	 * @return  array
	 */
	protected function getTags(SymfonyStyle $symfonyStyle): array
	{
		$tags = [];

		$symfonyStyle->comment('Fetching page 1 of tags.');

		// Get the first page so we can process the headers to figure out how many times we need to do this
		$tags = array_merge($tags, $this->github->repositories->getTags($this->repoOwner, $this->repoName, 1));

		$response = $this->github->repositories->getApiResponse();

		if ($response->hasHeader('Link'))
		{
			preg_match('/(\?page=[0-9]+>; rel=\"last\")/', $response->getHeader('Link')[0], $matches);

			if ($matches && isset($matches[0]))
			{
				preg_match('/\d+/', $matches[0], $pages);

				$lastPage = $pages[0];

				for ($page = 2; $page <= $lastPage; $page++)
				{
					$symfonyStyle->comment(sprintf('Fetching page %d of %d pages of tags.', $page, $lastPage));

					$tags = array_merge($tags, $this->github->repositories->getTags($this->repoOwner, $this->repoName, $page));
				}
			}
		}

		return $tags;
	}
}
