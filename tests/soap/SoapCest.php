<?php
namespace soap;

use SoapTester;

class SoapCest {
    public function _before(SoapTester $I)
    {
    }

    // tests
    public function wsdlTest(SoapTester $I)
    {
        $I->amOnPage('api');
        $I->canSeeResponseCodeIs(200);
        $response = $I->grabPageSource();
        $I->assertXmlMatches('//wsdl:service', $response);
	    $I->assertXmlMatches("//wsdl:operation[@name='soapTest']", $response);
    }

	/**
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function mirrorTest(SoapTester $I)
    {
	    $namespace = 'urn:uhi67/services/tests/app/controllers/ApiControllerwsdl';
	    $method = 'mirror';
	    $endpoint = 'http://localhost:8080/api?ws=1';
	    $I->sendSoapRequest($method, '<params><a>x</a></params>');
	    $expectedRequest = <<<EOT
<soapenv:Envelope xmlns:ns="$namespace" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
	<soapenv:Header xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"/>
	<soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
		<ns:$method>
			<params><a>x</a></params>
		</ns:$method>
	</soapenv:Body>
</soapenv:Envelope>
EOT;
	    $request = $I->grabSoapRequest();
	    $I->assertXmlStringEqualsXmlString($expectedRequest, $request->saveXML());

	    $expectedResponse = <<<EOT
<SOAP-ENV:Envelope 
		xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
		xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
		xmlns:ns1="$namespace" 
		xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
		SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
	<SOAP-ENV:Body>
		<ns1:{$method}Response>
			
		</ns1:{$method}Response>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;

	    $response = $I->grabSoapResponse();
	    codecept_debug($response->saveXML());
	    $I->seeSoapResponseContainsXPath("//*[local-name()='Envelope']"); // Ignore NS
	    $I->seeSoapResponseContainsXPath('//SOAP-ENV:Envelope/SOAP-ENV:Body');
	    $I->cantSeeSoapResponseContainsXPath('//SOAP-ENV:Envelope/SOAP-ENV:Body/SOAP-ENV:Fault');
	    $I->seeSoapResponseContainsXPath('//ns1:Get_DepartmentResponse');


    }
}
