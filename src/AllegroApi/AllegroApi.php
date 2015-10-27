<?php

namespace AllegroApi;

use SoapClient;

require_once "AllegroApiException.php";

class AllegroApi
{
	const WSDL = 'https://webapi.allegro.pl/service.php?wsdl';
	const WSDL_SANDBOX = 'https://webapi.allegro.pl.webapisandbox.pl/service.php?wsdl';

	/**
	 * @var int
	 */
	public $country_code;

	/**
	 * @var SoapClient
	 */
	private $client;

	/**
	 * @var stdClass|null
	 */
	private $session = null;

	/**
	 * @var stdClass
	 */
	private $config;

	/**
	 * @var array
	 */
	private $request;

	/**
	 * @param string $login
	 * @param string $hashPassword
	 * @param string $apiKey
	 * @param bool $sandbox
	 * @param int $countryCode
	 */
	public function __construct($login, $hashPassword, $apiKey, $sandbox, $countryCode)
	{
		$this->validateContructorParams($login, $hashPassword, $apiKey, $sandbox, $countryCode);

		$this->config = (object) [
			'login'			=> $login,
			'hashPassword'	=> $hashPassword,
			'apikey'		=> $apiKey,
			'sandbox'		=> $sandbox,
			'countryCode'	=> $countryCode,
		];

		//math wsdl
		$wsdl = (isset($this->config->sandbox) && (int)$this->config->sandbox) ? self::WSDL_SANDBOX : self::WSDL;

		// crete client
		$this->client = new SoapClient(
			$wsdl,
			[
				'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
			]
		);

		// create request id data
		$this->request = [
			'countryId' => $this->config->countryCode, // for old function - example: doGetShipmentData
			'countryCode' => $this->config->countryCode, // for new function
			'webapiKey' => $this->config->apikey,
			'localVersion' => $this->loadVersionKey($this->config->countryCode)
		];
	}

	/**
	 * Do some magic
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return type
	 */
	public function __call($name, array $arguments = [])
	{
		// prepare data
		$params = isset($arguments[0]) ? (array) $arguments[0] : [];
		$request = $this->buildRequest($params);

		// add 'do' to short function name
		$fname = 'do' . ucfirst($name);

		// call SOAP function
		return $this->client->$fname($request);
	}

	public function loginEnc()
	{
		// prevents
		if (!$this->config->hashPassword) {
			throw new AllegroApiException("hashPassword is required");
		}

		// login enc
		$request = $this->buildRequest([
			'userLogin' => $this->config->login,
			'userHashPassword' => $this->config->hashPassword
		]);

		// add session to request
		$this->session = $this->client->doLoginEnc($request);
		$this->request['sessionId'] = $this->session->sessionHandlePart;    // for new functions
		$this->request['sessionHandle'] = $this->session->sessionHandlePart; // for older functions
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function buildRequest(array $data)
	{
		return array_replace_recursive($this->request, (array) $data);
	}

	/**
	 * @param int $countryCode
	 * @return type
	 * @throws Exception
	 */
	private function loadVersionKey($countryCode)
	{
		$sys = $this->client->doQueryAllSysStatus([
			'countryId' => $this->config->countryCode,
			'webapiKey' => $this->config->apikey
		]);

		foreach ($sys->sysCountryStatus->item as $row) {
			if ($countryCode === $row->countryId) {
				return $row->verKey;
			}
		}

		throw new Exception(sprintf('I didnt find any country by code %s', $countryCode));
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
