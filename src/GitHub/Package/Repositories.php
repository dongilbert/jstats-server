<?php

namespace Stats\GitHub\Package;

use Joomla\Github\Package\Repositories as BaseRepositories;
use Joomla\Http\Response;

/**
 * Extended GitHub API Repositories class.
 *
 * @since  1.0
 */
class Repositories extends BaseRepositories
{
	/**
	 * API Response object
	 *
	 * @var    Response
	 * @since  1.0
	 */
	private $apiResponse;

	/**
	 * Get the last API response if one is set
	 *
	 * @return  Response|null
	 *
	 * @since   1.0
	 */
	public function getApiResponse()
	{
		return $this->apiResponse;
	}

	/**
	 * Get a list of tags on a repository.
	 *
	 * Note: This is different from the parent `getListTags` method as it adds support for the API's pagination. This extended method can be removed
	 * if the upstream class gains this support.
	 *
	 * @param   string   $owner  Repository owner.
	 * @param   string   $repo   Repository name.
	 * @param   integer  $page   The page number from which to get items.
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function getTags($owner, $repo, $page = 0)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/tags';

		// Send the request.
		$this->apiResponse = $this->client->get($this->fetchUrl($path, $page));

		return $this->processResponse($this->apiResponse);
	}
}
