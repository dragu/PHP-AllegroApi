<?php
namespace Tests;

require __DIR__ . "/../src/AllegroApi/AllegroApi.php";
require "ConfigDistributor.php";

use AllegroApi\AllegroApi;

class AllegroApiTest extends \PHPUnit_Framework_TestCase
{

	public function testCanLoginEnc()
	{
		// config
		$config = ConfigDistributor::getInstance()->getConfig();

		// create an object
		$allegroApi = new AllegroApi($config->login, $config->hashPassword, $config->apikey, $config->sandbox, $config->countryCode);

		/**
		 * test plain login
		 * ok, if no exception
		 */
		$throwsException = false;

		try {
			$allegroApi->loginEnc();
		} catch (Exception $ex) {
			$throwsException = true;
		}

		// doesn't throw an exception on success
		$this->assertEquals(false, $throwsException);
	}

	public function testCanExecuteRemoteFunction()
	{
		$config = ConfigDistributor::getInstance()->getConfig();
		$allegroApi = new AllegroApi($config->login, $config->hashPassword, $config->apikey, $config->sandbox, $config->countryCode);

		// execute remote function
		$throwsException = false;

		try {
			$allegroApi->getCountries();
		} catch (Exception $ex) {
			$throwsException = true;
		}

		$this->assertEquals(false, $throwsException);
	}

	public function testDoesExecuteResultIsObject()
	{
		$config = ConfigDistributor::getInstance()->getConfig();
		$allegroApi = new AllegroApi($config->login, $config->hashPassword, $config->apikey, $config->sandbox, $config->countryCode);

		$result = $allegroApi->getCountries();
		$this->assertEquals(true, is_object($result));
	}
}
