<?php

namespace Tests;

class ConfigDistributor {

	private static $instance = null;
	private $config = null;

	function __construct() {
		$configPath = __DIR__ . '/../config/config.ini';
		$data = parse_ini_file($configPath);

		if ($data == null) {
			throw new \Exception("No read or decode config.ini file");
		}

		$this->config = json_decode(json_encode($data));

		if (!$this->config->login) {
			throw new \Exception("Must set login for Allegro API server");
		}
		if (!$this->config->hashPassword) {
			throw new \Exception("Must set password for Allegro API server");
		}
		if (!$this->config->apikey) {
			throw new \Exception("Must set apikey for Allegro API server");
		}
		if (!isset($this->config->sandbox)) {
			throw new \Exception("Must set sandbox flag for Allegro API server");
		}
		if (!$this->config->countryCode) {
			throw new \Exception("Must set countryCode for Allegro API server");
		}

		$this->config->sandbox = (bool) $this->config->sandbox;
		$this->config->countryCode = (int) $this->config->countryCode;
	}

	public function getConfig() {
		return $this->config;
	}

	public function getParameters() {
		return array(
			'login' => $this->config->login,
			'hashPassword' => $this->config->hashPassword,
			'sandbox' => $this->config->sandbox,
			'appkey' => $this->config->appkey,
			'countryCode' => $this->config->countryCode
		);
	}


	/*
	 * Singleton
	 */

	/**
	 * @return ConfigDistributor
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new ConfigDistributor();
		}
		return self::$instance;
	}
}
