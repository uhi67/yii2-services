<?php /** @noinspection PhpUnused */

namespace soap;

use Codeception\Exception\ModuleException;
use Codeception\Module\SOAP;
use Codeception\Module\XmlAsserts;
use Codeception\Util\XmlBuilder;
use DOMNodeList;
use SoapFault;
use SoapTester;
use SoapVar;
use uhi67\soapHelper\SoapClientDry;

class SoapCest {
    public $wsdlFile;

    public function _before(SoapTester $I)
    {
        $this->wsdlFile = null;
        $I->amOnPage('sample-api');
        $wsdl = $I->grabPageSource();
        if($wsdl) {
            $this->wsdlFile = dirname(__DIR__). '/_output/wsdl-cest-'.md5(__CLASS__).'.xml';
            file_put_contents($this->wsdlFile, XmlAsserts::toXml($wsdl)->saveXml());
        }
    }

    public function _after() {
    }

    // tests
    public function wsdlTest(SoapTester $I)
    {
        $I->amOnPage('sample-api');
        $I->canSeeResponseCodeIs(200);
        $response = $I->grabPageSource();
        $I->assertXmlMatches('//wsdl:service', $response);
	    $I->assertXmlMatches("//wsdl:operation[@name='soapTest']", $response);
    }

	/**
	 * @throws ModuleException
	 */
	public function mirrorTest(SoapTester $I)
    {
        $soapEnvScheme = SOAP::SCHEME_SOAP_ENVELOPE;
	    $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
	    $method = 'mirror';
	    $I->sendSoapRequest($method, '<root><aaa>x</aaa></root>');
	    $expectedRequest = <<<EOT
<soapenv:Envelope xmlns:soapenv="$soapEnvScheme">
	<soapenv:Header/>
	<soapenv:Body>
		<ns:$method xmlns:ns="$namespace">
			<aaa>x</aaa>
		</ns:$method>
	</soapenv:Body>
</soapenv:Envelope>
EOT;
	    $request = $I->grabSoapRequest();
	    $I->assertXmlStringEqualsXmlString($expectedRequest, $request->saveXML());

        /** @noinspection PhpUnusedLocalVariableInspection */
        /** @noinspection XmlUnusedNamespaceDeclaration */
        $expectedResponse = <<<EOT
<SOAP-ENV:Envelope
		xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
		xmlns:SOAP-ENV="$soapEnvScheme" 
		xmlns:ns1="$namespace" 
		xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
		SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
	<SOAP-ENV:Body>
		<ns1:{$method}Response>
			<return xsi:type="xsd:string">x</return>
		</ns1:{$method}Response>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;

	    //$response = SoapUtils::toXml($I->grabPageSource()); // framework module catches output first, SOAP module gets nothing
	    $response = $I->grabSoapResponse(); // Only the corrected SOAP module delivers the result.
	    codecept_debug('Response='.$response->saveXML());
	    $I->seeSoapResponseContainsXPath("//*[local-name()='Envelope']"); // Ignore NS
	    $I->seeSoapResponseContainsXPath('//SOAP-ENV:Envelope/SOAP-ENV:Body');
	    $I->cantSeeSoapResponseContainsXPath('//SOAP-ENV:Envelope/SOAP-ENV:Body/SOAP-ENV:Fault');
	    $I->seeSoapResponseContainsXPath('//ns1:mirrorResponse');
        $I->seeSoapResponseContainsXPath("//return[text()='x']");
    }


    /**
     * @throws ModuleException
     */
    public function objectTest(SoapTester $I)
    {
        $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
        $method = 'getObject';
        $I->sendSoapRequest($method, '<root><params><a>x</a><b>13</b><c>29</c></params></root>');
        $response = $I->grabSoapResponse(); // Only the corrected SOAP module delivers the result.
        codecept_debug('Response='.$response->saveXML());

        // Check Response object and namespace
        $responseNodeList = XmlAsserts::xmlEval("//*[local-name()='getObjectResponse']", $response);
        $I->assertInstanceOf(DOMNodeList::class, $responseNodeList);
        $responseNode = $responseNodeList->item(0);
        $I->assertInstanceOf(DOMNodeList::class, $responseNodeList);
        $I->assertEquals('ns1:getObjectResponse', $responseNode->nodeName);
        $I->assertEquals($namespace, $responseNode->namespaceURI);

        // Check response structure
        $I->seeSoapResponseContainsXPath('//ns1:getObjectResponse');
        $expectedResult = /** @lang */ <<<EOT
<return xsi:type="SOAP-ENC:Struct" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <a xsi:type="xsd:string">x</a><b xsi:type="xsd:string">13</b><c xsi:type="xsd:string">29</c>
</return>
EOT;
        $resultList = XmlAsserts::xmlEval('//ns1:getObjectResponse/return', $response, ['ns1'=>$namespace]);
        $I->assertCount(1, $resultList);
        $I->assertXmlStringEqualsXmlString($expectedResult, XmlAsserts::toXml($resultList[0]));
    }

    /**
     * @throws ModuleException
     */
    public function object2Test(SoapTester $I)
    {
        $soapEnvScheme = SOAP::SCHEME_SOAP_ENVELOPE;
        $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
        $method = 'getObject2';
        /** @var XmlBuilder $requestBuilder */
        $I->sendSoapRequest($method, ['a'=>13, 'b'=>true, 'c'=>'foo'], $this->wsdlFile); // pass arguments as associative array with argument names

        $expectedRequest = /* @lang XMLs */<<<EOT
<SOAP-ENV:Envelope xmlns:SOAP-ENV="$soapEnvScheme" xmlns:ns1="$namespace" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
	<SOAP-ENV:Body>
		<ns1:$method>
            <a xsi:type="xsd:int">13</a>
            <b xsi:type="xsd:boolean">true</b>
            <c xsi:type="xsd:string">foo</c>
		</ns1:$method>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;
        $request = $I->grabSoapRequest();
        $I->assertXmlStringEqualsXmlString($expectedRequest, $request->saveXML());

        $response = $I->grabSoapResponse(); // Only the corrected SOAP module delivers the result.
        codecept_debug('Response='.$response->saveXML());
        $I->seeSoapResponseContainsXPath('//ns1:getObject2Response');

        $expectedResult = /** @lang */<<<EOT
<return xsi:type="ns1:MyObject" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <a xsi:type="xsd:int">13</a>
    <b xsi:type="xsd:boolean">true</b>
    <c xsi:type="xsd:string">foo</c>
</return>
EOT;

        $resultList = XmlAsserts::xmlEval('//ns1:getObject2Response/return', $response, ['ns1'=>$namespace]);
        $I->assertCount(1, $resultList);
        $I->assertXmlStringEqualsXmlString($expectedResult, XmlAsserts::toXml($resultList[0]));
    }

    /**
     * @throws ModuleException
     */
    public function getStdClassTest(SoapTester $I)
    {
        $soapEnvScheme = SOAP::SCHEME_SOAP_ENVELOPE;
        $soapEncoding = SOAP::SCHEME_SOAP_ENCODING;
        $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
        $method = 'getStdClass';
        $a = ['alma', true, 3];

        $I->sendSoapRequest($method, ['a'=>$a], $this->wsdlFile);

        $response = $I->grabSoapResponse();
        codecept_debug("Response=".$response->saveXML());

        $expectedResponse = /** @lang */<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="$soapEnvScheme" 
            xmlns:ns1="$namespace" 
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
            xmlns:SOAP-ENC="$soapEncoding" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        SOAP-ENV:encodingStyle="$soapEncoding"
>
    <SOAP-ENV:Body>
        <ns1:getStdClassResponse>
            <return xsi:type="SOAP-ENC:Struct">
                <arr SOAP-ENC:arrayType="xsd:ur-type[3]" xsi:type="SOAP-ENC:Array">
                    <item xsi:type="xsd:string">alma</item>
                    <item xsi:type="xsd:boolean">true</item>
                    <item xsi:type="xsd:int">3</item>
                </arr>
            </return>
        </ns1:getStdClassResponse>
    </SOAP-ENV:Body></SOAP-ENV:Envelope>
EOT;

        /** @noinspection PhpParamsInspection */
        $I->assertXmlStringEqualsXmlString($expectedResponse, $response);
    }

    /**
     * @throws ModuleException
     * @throws SoapFault
     */
    public function getDotNetObjectTest(SoapTester $I)
    {
        $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
        $method = 'getDotNetObject';
        $parameters = ['params'=>new SoapVar(['aa'=>23, 'bb'=>'banán', 'cc'=>[77,88,99]], SOAP_ENC_OBJECT)];

        $requestXml = SoapClientDry::__requestXml($parameters, $method, $this->wsdlFile);

        $expectedRequest = /** @lang XMLs */ <<<EOT
<SOAP-ENV:Envelope xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
    <SOAP-ENV:Body>
        <ns1:getDotNetObject>
            <params xsi:type="SOAP-ENC:Struct">
                <aa xsi:type="xsd:int">23</aa>
                <bb xsi:type="xsd:string">banán</bb>
                <cc xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:int[3]">
                      <item xsi:type="xsd:int">77</item>
                      <item xsi:type="xsd:int">88</item>
                      <item xsi:type="xsd:int">99</item>
                </cc>
            </params>
        </ns1:getDotNetObject>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;
        $I->assertXmlStringEqualsXmlString($expectedRequest, $requestXml);

        $I->sendSoapRequest($method, $parameters, $this->wsdlFile);
        $request = $I->grabSoapRequest();
        codecept_debug("Request=".$request->saveXML());
        /** @noinspection PhpParamsInspection */
        $I->assertXmlStringEqualsXmlString($expectedRequest, $request);

        $response = $I->grabSoapResponse();
        codecept_debug("Response=".$response->saveXML());

        $expectedResponse = /** @lang XMLs */<<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope 
            xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" 
            xmlns:ns1="$namespace" 
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
            xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
>
    <SOAP-ENV:Body>
        <ns1:getDotNetObjectResponse>
            <return xsi:type="SOAP-ENC:Struct">
                <getDotNetObjectResult xsi:type="SOAP-ENC:Struct">
                    <aa xsi:type="xsd:int">23</aa>
                    <bb xsi:type="xsd:string">banán</bb>
                    <cc xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:int[3]">
                          <item xsi:type="xsd:int">77</item>
                          <item xsi:type="xsd:int">88</item>
                          <item xsi:type="xsd:int">99</item>
                    </cc>
                </getDotNetObjectResult>  
            </return>
        </ns1:getDotNetObjectResponse>
    </SOAP-ENV:Body></SOAP-ENV:Envelope>
EOT;

        /** @noinspection PhpParamsInspection */
        $I->assertXmlStringEqualsXmlString($expectedResponse, $response);
    }
}
