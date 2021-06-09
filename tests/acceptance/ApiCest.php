<?php /** @noinspection PhpUnused */

namespace acceptance;

use AcceptanceTester;
use SoapClient;
use SoapFault;
use stdClass;

class ApiCest
{
    public $wsdl;

    public function _before()
    {
        $this->wsdl = 'http://localhost:8080/sample-api';
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('sample-api');
        $I->seeResponseCodeIs(200);
        $I->seeInSource('<definitions');
        $I->seeInSource('<wsdl:operation name="getObject">');
        $I->seeInSource('<wsdl:operation name="mirror">');
    }

	/**
	 * Acceptance test runs soap call thru a web server.
     * Please start web server on port 8080, e.g. `php tests/app/yii serve`
	 *
	 * @throws SoapFault
	 */
	function mirrorWsTest(AcceptanceTester $I) {
		$param = 13;
		$method = 'mirror';

		$client = new SoapClient($this->wsdl, [
			'cache_wsdl'=>WSDL_CACHE_NONE,
			'cache_wsdl_ttl'=>0,
//            'trace' => true,
		]);

		$soapResult = $client->__soapCall($method, ['parameters'=>$param], ['exceptions' => 1]);
        codecept_debug('Result='.print_r($soapResult, true));

		$I->assertFalse(is_soap_fault($soapResult));
        $expected = 13;
		$I->assertEquals($expected, $soapResult);

//		$response = $client->__getLastResponse();   // turn on trace first
//        codecept_debug('Response='.print_r($response, true));

	}

    /**
     * Acceptance test runs soap call thru a web server.
     * Please start web server, e.g. `php tests/app/yii serve`
     *
     * @throws SoapFault
     */
    function getStdClassWsTest(AcceptanceTester $I) {
        $method = 'getStdClass';
        $arrayValue = [13, true, 'citrom'];

        $client = new SoapClient($this->wsdl, [
            'cache_wsdl'=>WSDL_CACHE_NONE,
            'cache_wsdl_ttl'=>0,
            'trace' => true,
        ]);

        $soapResult = $client->__soapCall($method, [$arrayValue], ['exceptions' => 0]);
        $request = $client->__getLastRequest();   // turn on trace first
        codecept_debug('Request='.print_r($request, true));

        $I->assertFalse(is_soap_fault($soapResult));
		$response = $client->__getLastResponse();   // turn on trace first
        codecept_debug('Response='.print_r($response, true));

        codecept_debug('Result='.print_r($soapResult, true));

        $expected = new stdClass();
        $expected->arr = $arrayValue;
        $I->assertEquals($expected, $soapResult);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $expectedRequest = /** @lang */<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
        xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
        xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" 
        xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
        xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
>
    <SOAP-ENV:Body>
        <ns1:getStdClass>
            <a SOAP-ENC:arrayType="xsd:string[3]" xsi:type="SOAP-ENC:Array">
                <item xsi:type="xsd:string">alma</item>
                <item xsi:type="xsd:string">banán</item>
                <item xsi:type="xsd:string">citrom</item>
            </a>
        </ns1:getStdClass>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $expectedResponse = /* @lang */ <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
        xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
        xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" 
        xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
>
    <SOAP-ENV:Body>
        <ns1:getStdClassResponse>
            <return xsi:type="SOAP-ENC:Struct">
                <arr SOAP-ENC:arrayType="xsd:string[3]" xsi:type="SOAP-ENC:Array">
                    <item xsi:type="xsd:string">alma</item>
                    <item xsi:type="xsd:string">banán</item>
                    <item xsi:type="xsd:string">citrom</item>
                </arr>
            </return>
        </ns1:getStdClassResponse>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;

    }


    function getObjectTest(AcceptanceTester $I) {
        $method = 'getObject';
        $arrayValue = ['a'=>13, 'b'=>true, 'c'=>'citrom'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $client = new SoapClient($this->wsdl, [
            'cache_wsdl'=>WSDL_CACHE_NONE,
            'cache_wsdl_ttl'=>0,
            'trace' => true,
        ]);
        $soapResult = $client->__soapCall($method, [$arrayValue], ['exceptions' => 0]);
        codecept_debug('Request='.print_r($client->__getLastRequest(), true));
        codecept_debug('Response='.print_r($client->__getLastResponse(), true));
        codecept_debug('Result='.print_r($soapResult, true));
        $I->assertEquals((object)$arrayValue, $soapResult);

    }

}
