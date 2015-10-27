<?php
namespace Tests;

require __DIR__ . "/../src/AllegroApi/AllegroApi.php";
require "ConfigDistributor.php";

use AllegroApi\AllegroApi;

class AllegroApiTest extends \PHPUnit_Framework_TestCase
{

	public function testCanLoginEnc()
	{
		//config
		$config = ConfigDistributor::getInstance()->getConfig();

		//create object
		$allegroApi = new AllegroApi($config->login, $config->hashPassword, $config->apikey, $config->sandbox, $config->countryCode);

		//test plain login
		// ok, if no exception
		$throwException = false;
		try {
			$allegroApi->loginEnc();
		} catch (Exception $ex) {
			$throwException = true;
		}
		//no throw a exception if success
		$this->assertEquals(false, $throwException);
	}

	public function testCanExecuteRemoteFunction()
	{
		//config
		$config = ConfigDistributor::getInstance()->getConfig();

		//create object
		$allegroApi = new AllegroApi($config->login, $config->hashPassword, $config->apikey, $config->sandbox, $config->countryCode);

		//execute remote function
		$throwException = false;
		try {
			$allegroApi->getCountries();
		} catch (Exception $ex) {
			$throwException = true;
		}
		//no throw a exception if success
		$this->assertEquals(false, $throwException);
	}

	public function testDoesExecuteResultIsObject()
	{
		//config
		$config = ConfigDistributor::getInstance()->getConfig();

		//create object
		$allegroApi = new AllegroApi($config->login, $config->hashPassword, $config->apikey, $config->sandbox, $config->countryCode);

		//execute remote function
		$result = $allegroApi->getCountries();
		$this->assertEquals(true, is_object($result));
	}
}
