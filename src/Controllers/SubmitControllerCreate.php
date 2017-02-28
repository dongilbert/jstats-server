<?php
/**
 * Joomla! Statistics Server
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\StatsServer\Controllers;

use Joomla\Controller\AbstractController;
use Joomla\StatsServer\Decorators\ValidateVersion;
use Joomla\StatsServer\Models\StatsModel;

/**
 * Controller for processing submitted statistics data.
 *
 * @method         \Joomla\StatsServer\WebApplication  getApplication()  Get the application object.
 * @property-read  \Joomla\StatsServer\WebApplication  $app              Application object
 *
 * @since          1.0
 */
class SubmitControllerCreate extends AbstractController
{
	use ValidateVersion;

	/**
	 * Statistics model object.
	 *
	 * @var    StatsModel
	 * @since  1.0
	 */
	private $model;

	/**
	 * Allowed Database Types.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $databaseTypes = [
		'mysql',
		'mysqli',
		'pdomysql',
		'postgresql',
		'sqlazure',
		'sqlsrv',
	];

	/**
	 * Constructor.
	 *
	 * @param   StatsModel  $model  Statistics model object.
	 *
	 * @since   1.0
	 */
	public function __construct(StatsModel $model)
	{
		$this->model = $model;
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
		$input = $this->getInput();

		$data = [
			'php_version' => $input->getRaw('php_version', ''),
			'db_version'  => $input->getRaw('db_version', ''),
			'cms_version' => $input->getRaw('cms_version', ''),
			'unique_id'   => $input->getString('unique_id'),
			'db_type'     => $input->getString('db_type', ''),
			'server_os'   => $input->getString('server_os'),
		];

		// Backup the original POST before manipulating/validating data
		$originalData = $data;

		// Validate the submitted data
		$data['php_version'] = $this->checkPHPVersion($data['php_version']);
		$data['cms_version'] = $this->checkCMSVersion($data['cms_version']);
		$data['db_type']     = $this->checkDatabaseType($data['db_type']);
		$data['db_version']  = $this->validateVersionNumber($data['db_version']);

		// We require at a minimum a unique ID and the CMS version
		if (empty($data['unique_id']) || empty($data['cms_version']))
		{
			$this->getApplication()->getLogger()->info(
				'Missing required data from request.',
				['postData' => $originalData]
			);

			$response = [
				'error'   => true,
				'message' => 'There was an error storing the data.',
			];

			$this->getApplication()->setHeader('HTTP/1.1 500 Internal Server Error', 500, true);
			$this->getApplication()->setBody(json_encode($response));

			return true;
		}

		// If the below data does not pass tests, we do not accept the POST
		if ($data['php_version'] === false || $data['cms_version'] === false || $data['db_type'] === false || $data['db_version'] === false)
		{
			$response = [
				'error'   => true,
				'message' => 'Invalid data submission.',
			];

			$this->getApplication()->setHeader('HTTP/1.1 500 Internal Server Error', 500, true);
			$this->getApplication()->setBody(json_encode($response));

			return true;
		}

		$this->model->save((object) $data);

		$response = [
			'error'   => false,
			'message' => 'Data saved successfully',
		];

		$this->getApplication()->setBody(json_encode($response));

		return true;
	}

	/**
	 * Check the CMS version.
	 *
	 * @param   string  $version  The version number to check.
	 *
	 * @return  string|boolean  The version number on success or boolean false on failure.
	 *
	 * @since   1.0
	 */
	private function checkCMSVersion(string $version)
	{
		$version = $this->validateVersionNumber($version);

		// If the version number is invalid, don't go any further
		if ($version === false)
		{
			return false;
		}

		// Joomla only uses major.minor.patch so everything else is invalid
		$explodedVersion = explode('.', $version);

		if (count($explodedVersion) > 3)
		{
			return false;
		}

		// Import the valid release listing
		$path = APPROOT . '/versions/joomla.json';

		if (!file_exists($path))
		{
			throw new \RuntimeException('Missing Joomla! release listing', 500);
		}

		$validVersions = json_decode(file_get_contents($path), true);

		// Check that the version is in our valid release list
		if (!in_array($version, $validVersions))
		{
			return false;
		}

		return $version;
	}

	/**
	 * Check the database type
	 *
	 * @param   string  $database  The database type to check.
	 *
	 * @return  string|boolean  The database type on success or boolean false on failure.
	 *
	 * @since   1.0
	 */
	private function checkDatabaseType(string $database)
	{
		if (!in_array($database, $this->databaseTypes))
		{
			return false;
		}

		return $database;
	}

	/**
	 * Check the PHP version
	 *
	 * @param   string  $version  The version number to check.
	 *
	 * @return  string|boolean  The version number on success or boolean false on failure.
	 *
	 * @since   1.0
	 */
	private function checkPHPVersion(string $version)
	{
		$version = $this->validateVersionNumber($version);

		// If the version number is invalid, don't go any further
		if ($version === false)
		{
			return false;
		}

		// We only track versions based on major.minor.patch so everything else is invalid
		$explodedVersion = explode('.', $version);

		if (count($explodedVersion) > 3)
		{
			return false;
		}

		// Import the valid release listing
		$path = APPROOT . '/versions/php.json';

		if (!file_exists($path))
		{
			throw new \RuntimeException('Missing PHP release listing', 500);
		}

		$validVersions = json_decode(file_get_contents($path), true);

		// Check that the version is in our valid release list
		if (!in_array($version, $validVersions))
		{
			return false;
		}

		return $version;
	}
}
