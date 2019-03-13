<?php

namespace app\Helpers;

use SoapClient as PHPSoap;
use SoapFault;
use SimpleXMLElement;

class SoapClient
{
	private static $instance = null;
	private static $location = null;
	private static $options = [];
	private static $wsdl;
	private static $parameters;

	public static function wsdl(string $wsdl)
	{
		if (Self::$instance === null) {
			Self::$instance = new Self;
		}
		Self::$wsdl = $wsdl;
		return Self::$instance;
	}

	public function location(string $location)
	{
		Self::$location = ['location' => $location];
		return $this;
	}

	public function parameters(array $parameters)
	{
		Self::$parameters = $parameters;
		return $this;
	}

	public function call(string $functionName)
	{
		$client = new PHPSoap(Self::$wsdl, Self::$options);
		try {
			return $client->__soapCall($functionName, Self::$parameters, Self::$location);
		} catch (SoapFault $e) {
			return false;
		}
	}

	//OPTIONS
	public function trace(bool $trace)
	{
		if ($trace) {
			Self::$options = array_merge(Self::$options, ['trace' => 1]);
		}
		return $this;
	}

	public function exception(bool $exception)
	{
		if ($exception) {
			Self::$options = array_merge(Self::$options, ['exception' => 1]);
		}
		return $this;
	}

	//FORMATTERS
	public static function parseXML($xml)
	{
		$object = new SimpleXMLElement($xml);
		$object = json_encode($object);
		$object = json_decode($object);
		return $object;
	}

}