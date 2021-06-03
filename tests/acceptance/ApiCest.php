<?php /** @noinspection PhpUnused */

namespace acceptance;

use AcceptanceTester;
use SoapClient;
use SoapFault;

class ApiCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
        $I->sendGET('api/soap');
        $I->seeResponseCodeIs(200);
    }

	/**
	 * Acceptance test runs soap call thru a web server.
     * Please start web server on port 8080, e.g. `php tests/app/yii serve`
	 *
	 * @throws SoapFault
	 */
	function mirrorWsTest(AcceptanceTester $I) {
		$params = 13;
		$wsdl = 'http://localhost:8080/api/soap';
		$method = 'mirror';

		$client = new SoapClient($wsdl, [
			'cache_wsdl'=>WSDL_CACHE_NONE,
			'cache_wsdl_ttl'=>0,
		]);

		$soapResult = $client->__soapCall($method, ['parameters'=>$params], ['exceptions' => 1]);
        codecept_debug('Result='.print_r($soapResult, true));

		$I->assertFalse(is_soap_fault($soapResult));
        $expected = 13;
		$I->assertEquals($expected, $soapResult);

	}
}
