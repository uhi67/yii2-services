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
        $I->amOnPage('api/soap');
        $I->canSeeResponseCodeIs(200);
        $response = $I->grabPageSource();
        $I->assertXmlMatches('//wsdl:service', $response);
        $fragment = '<wsdl:service name="ApiControllerService" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="urn:uhi67/services/tests/app/controllers/ApiControllerwsdl">
<wsdl:port name="ApiControllerPort" binding="tns:ApiControllerBinding">
<soap:address location="http://localhost:8080/api/soap?ws=1"/>
</wsdl:port>
</wsdl:service>';
    }
}
