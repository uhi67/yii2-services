<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnused */

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;

/**
 * Class SoapModule
 *
 * SOAP helper actions and assertions. Requires SOAP module.
 *
 * @package Helper
 */
class SoapModule extends Module {
	/**
	 * @throws \Codeception\Exception\ModuleException
	 * @throws \Codeception\Exception\ModuleConfigException
	 * @see https://stackoverflow.com/questions/34872451/codeception-soap-namespace
	 */
	public function configure($endpoint, $schema) {
		$this->getModule('SOAP')->_reconfigure([
			'endpoint' => $endpoint,
			'schema' => $schema,
		]);
	}
	/**
	 * @return \DOMDocument -- // Don't replace with alias, build will fail
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function grabSoapRequest() {
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		return $this->getModule('SOAP')->xmlRequest;
	}

	/**
	 * @return \DOMDocument
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function grabSoapResponse() {
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		return $this->getModule('SOAP')->xmlResponse;
	}

	/**
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function seeResponseIsValidOnSchema($schema) {
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		$response = $this->getModule('SOAP')->response;
		$this->assertTrue($response->schemaValidate($schema));
	}

	/**
	 * Cached WSDL files
	 */
	static private $schemas = [];

	/**
	 * Returns the location of the Wsdl file generated dinamically
	 *
	 * @param   string  $endpoint  The webservice url.
	 *
	 * @return mixed
	 * @see https://stackoverflow.com/questions/34872451/codeception-soap-namespace
	 */
	public function getSoapWsdlDinamically($endpoint)
	{
		// Gets cached WSDL static file from dynamic file
		if (!isset(self::$schemas[$endpoint]))
		{
			$wsdl = simplexml_load_file($endpoint . '?wsdl');
			$schema = $wsdl['targetNamespace'];
			self::$schemas[$endpoint] = $schema;
		}

		return self::$schemas[$endpoint];
	}
}
