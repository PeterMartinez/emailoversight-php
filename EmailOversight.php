<?php

namespace Bcismariu\EmailOversight;

class EmailOversight
{
	private $parameters = [
		'apitoken'	=> null,
		'listid'	=> null,
		'email'		=> null,
	];
	protected $url = 'https://api.emailoversight.com/api/';
	public function __construct($parameters = [])
	{
		if (gettype($parameters) == 'string') {
			$apitoken = $parameters;
			$parameters = [];
			$parameters['apitoken'] = $apitoken;
		}
		foreach ($parameters as $key => $value) {
			$this->setParameter($key, $value);
		}
	}
	public function emailValidation($email, $listid = null)
	{
		$this->setParameter('email', $email);
		if ($listid) {
			// if this is not set we will use the global value
			$this->setParameter('listid', $listid);
		}
		return $this->post('emailvalidation', ['email', 'listid']);
	}

	public function emailAppend($firstname,$lastname,$postalcode,$formattedaddress=null,$zip4=null)
	{
		$this->setParameter('firstname', $firstname);
		$this->setParameter('lastname', $lastname);
		$this->setParameter('postalcode', $postalcode);
		$this->setParameter('formattedaddress', $formattedaddress);
		$this->setParameter('zip4', $zip4);
		return $this->post('emailappend', ['firstname', 'lastname','postalcode','formattedaddress','zip4']);
	}

	protected function get($method, $parameters = [])
	{
		$parameters['apitoken'] = $this->parameters['apitoken'];
		$query = $this->buildQuery($method, $parameters);
		$result = file_get_contents($query);
		return $this->parseResult($result);
	}
	protected function post($method, $parameters = [])
	{
		$options = [
			"ssl"=> [
		        	"verify_peer"=>false,
		        	"verify_peer_name"=>false,
    			],
			'http'	=> [
				'header'	=> "Content-type: application/json; charset=utf-8\r\n"
							 . "ApiToken: " . $this->parameters['apitoken'] . "\r\n",
				'method'	=> "POST",
				'content'	=> json_encode($this->getApiParameters($parameters)),
			],
		];
		$context = stream_context_create($options);
		$result = file_get_contents($this->url . $method, false, $context);
		return $this->parseResult($result);
	}
	protected function buildQuery($method, $parameters)
	{
		return $this->url . $method
			. '?' . http_build_query($this->getApiParameters($parameters));
	}
	protected function setParameter($key, $value = null)
	{
		$key = strtolower(trim($key));
		//Commented out because the variables will be dynamic based on endpoint.
		// if (!array_key_exists($key, $this->parameters)) {
		// 	return;
		// }
		$this->parameters[$key] = $value;
	}
	protected function getApiParameters($parameters)
	{
		$api = [];
		foreach ($parameters as $key) {
			$api[$key] = $this->parameters[$key];
		}
		return $api;
	}
	public function parseResult($result)
	{
		return json_decode($result, true);
	}
	
	public function isVerified($result)
	{
		if($result['Result'] == "Verified"){
			return true;
		}else {
			return false;
	 	}
	}
}
