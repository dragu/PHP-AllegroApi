<?php

namespace AllegroApi;

require_once "AllegroApiException.php";

class AllegroApi
{
	const WSDL = 'https://webapi.allegro.pl/service.php?wsdl';
	const WSDL_SANDBOX = 'https://webapi.allegro.pl.webapisandbox.pl/service.php?wsdl';

	//private
	private $client = null;
	private $session = null;
	private $config = null;
	private $request = null;

	//public
	public $country_code = null;

	/**
	 * @param string $login
	 * @param string $hashPassword
	 * @param string $apiKey
	 * @param bool $sandbox
	 * @param int $countryCode
	 */
	function __construct($login, $hashPassword, $apiKey, $sandbox, $countryCode)
	{
		$this->validateContructorParams($login, $hashPassword, $apiKey, $sandbox, $countryCode);

		//save data
		$this->config = (object) [
			'login'			=> $login,
			'hashPassword'	=> $hashPassword,
			'apikey'		=> $apiKey,
			'sandbox'		=> $sandbox,
			'countryCode'	=> $countryCode,
		];

		//math wsdl
		$wsdl = (isset($this->config->sandbox) && (int)$this->config->sandbox) ? self::WSDL_SANDBOX : self::WSDL;

		//crete client
		$this->client = new \SoapClient(
			$wsdl, array(
				'features' => SOAP_SINGLE_ELEMENT_ARRAYS
			)
		);

		//create request id data
		$this->request = array(
			'countryId' => $this->config->countryCode, //for old function - example: doGetShipmentData
			'countryCode' => $this->config->countryCode, //for new function
			'webapiKey' => $this->config->apikey,
			'localVersion' => $this->loadVersionKey($this->config->countryCode)
		);
	}

	function loginEnc()
	{
		//prevents
		if (!$this->config->hashPassword) {
			throw new AllegroApiException("No set sha256 hash password to login");
		}

		//login enc
		$request = $this->buildRequest(
			array(
				'userLogin' => $this->config->login,
				'userHashPassword' => $this->config->hashPassword
			)
		);

		//add session to request
		$this->session = $this->client->doLoginEnc($request);
		$this->request['sessionId'] = $this->session->sessionHandlePart;    //for new functions
		$this->request['sessionHandle'] = $this->session->sessionHandlePart; //for older functions
	}

	protected function buildRequest($data)
	{
		return array_replace_recursive($this->request, (array)$data);
	}

	protected function buildResponse($obj)
	{
		return $obj;
	}

	/*
	 * MAGIC METHODS
	 */

	function __call($name, $arguments)
	{
		//prepare data
		$params = isset($arguments[0]) ? (array)$arguments[0] : array();
		$request = $this->buildRequest($params);

		//add 'do' to short function name
		$fname = 'do' . ucfirst($name);

		//call SOAP function
		$responseData = $this->client->$fname($request);
		return $this->buildResponse($responseData);
	}

	/*
	 * HELPERS
	 */

	private function loadVersionKey($countryCode)
	{
		$sys = $this->client->doQueryAllSysStatus(
			array(
				'countryId' => $this->config->countryCode,
				'webapiKey' => $this->config->apikey
			)
		);
		foreach ($sys->sysCountryStatus->item as $row) {
			if ($row->countryId == $countryCode) {
				return $row->verKey;
			}
		}
		throw new Exception("No find country by code: ${$countryCode}");
	}

	/**
	 * @param string $login
	 * @param string $hashPassword
	 * @param string $apiKey
	 * @param bool $sandbox
	 * @param int $countryCode
	 */
	private function validateContructorParams($login, $hashPassword, $apiKey, $sandbox, $countryCode)
	{
		assert(strlen($login));
		assert(strlen($apiKey));
		assert($sandbox !== null && is_bool($sandbox));
		assert(is_int($countryCode) && $countryCode > 0);
		assert(strlen($hashPassword));
	}
}
