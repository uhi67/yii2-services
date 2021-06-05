<?php /** @noinspection PhpUnused */

namespace soap;

use Codeception\Exception\ModuleException;
use Codeception\Module\XmlAsserts;
use Codeception\Util\Soap;
use SoapTester;

class SoapCest {
    public $server;

    public function _before(SoapTester $I)
    {
    }

    public function _after() {
    }

    // tests
    public function wsdlTest(SoapTester $I)
    {
        $I->amOnPage('sample-api/soap');
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
        $soapEnvScheme = \Codeception\Module\SOAP::SCHEME_SOAP_ENVELOPE;
	    $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
	    $method = 'mirror';
	    $I->sendSoapRequest($method, '<aaa>x</aaa>');
	    $expectedRequest = <<<EOT
<soapenv:Envelope xmlns:ns="$namespace" xmlns:soapenv="$soapEnvScheme">
	<soapenv:Header/>
	<soapenv:Body>
		<ns:$method>
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
        $I->sendSoapRequest($method, '<params><a>x</a><b>13</b><c>29</c></params>');
        $response = $I->grabSoapResponse(); // Only the corrected SOAP module delivers the result.
        codecept_debug('Response='.$response->saveXML());

        // Check Response object and namespace
        $responseNodeList = XmlAsserts::xmlEval("//*[local-name()='getObjectResponse']", $response);
        $I->assertInstanceOf(\DOMNodeList::class, $responseNodeList);
        $responseNode = $responseNodeList->item(0);
        $I->assertInstanceOf(\DOMNodeList::class, $responseNodeList);
        $I->assertEquals('ns1:getObjectResponse', $responseNode->nodeName);
        $I->assertEquals($namespace, $responseNode->namespaceURI);

        // Check response structure
        $I->seeSoapResponseContainsXPath('//ns1:getObjectResponse');
        $expectedResult = /** @lang */ <<<EOT
<return xsi:type="SOAP-ENC:Struct" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <getObjectResult xsi:type="SOAP-ENC:Struct">
        <a xsi:type="xsd:string">x</a><b xsi:type="xsd:string">13</b><c xsi:type="xsd:string">29</c>
    </getObjectResult>
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
        $soapEnvScheme = \Codeception\Module\SOAP::SCHEME_SOAP_ENVELOPE;
        $namespace = 'urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl';
        $method = 'getObject2';
        /** @noinspection PhpUndefinedFieldInspection */
        $I->sendSoapRequest($method, Soap::request()->a->val(13)->parent()->b->val(true)->parent()->c->val('foo'));

        $expectedRequest = <<<EOT
<soapenv:Envelope xmlns:ns="$namespace" xmlns:soapenv="$soapEnvScheme">
	<soapenv:Header/>
	<soapenv:Body>
		<ns:$method>
            <a>13</a>
            <b>1</b>
            <c>foo</c>
		</ns:$method>
	</soapenv:Body>
</soapenv:Envelope>
EOT;
        $request = $I->grabSoapRequest();
        $I->assertXmlStringEqualsXmlString($expectedRequest, $request->saveXML());

        $response = $I->grabSoapResponse(); // Only the corrected SOAP module delivers the result.
        codecept_debug('Response='.$response->saveXML());
        $I->seeSoapResponseContainsXPath('//ns1:getObject2Response');

        $expectedResult = /** @lang */<<<EOT
<return xsi:type="SOAP-ENC:Struct" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <a xsi:type="xsd:int">13</a>
    <b xsi:type="xsd:boolean">true</b>
    <c xsi:type="xsd:string">foo</c>
</return>
EOT;

        $resultList = XmlAsserts::xmlEval('//ns1:getObject2Response/return', $response, ['ns1'=>$namespace]);
        $I->assertCount(1, $resultList);
        $I->assertXmlStringEqualsXmlString($expectedResult, XmlAsserts::toXml($resultList[0]));
    }
}
