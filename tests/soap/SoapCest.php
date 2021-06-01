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
    }
}
