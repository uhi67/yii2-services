<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;

class SoapModule extends Module {
	/**
	 * @throws ModuleException
	 * @throws ModuleConfigException
	 * @see https://stackoverflow.com/questions/34872451/codeception-soap-namespace
	 */
	public function configure($endpoint, $schema) {
		$this->getModule('SOAP')->_reconfigure([
			'endpoint' => $endpoint,
			'schema' => $schema,
		]);
	}
}
