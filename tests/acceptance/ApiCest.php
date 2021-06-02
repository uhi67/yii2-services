<?php /** @noinspection PhpUnused */

namespace acceptance;

use AcceptanceTester;

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
	 * Ez a teszt a fejlesztői verziót "kívülről" teszteli (webszervenek mennie kell)
	 *
	 * @throws \SoapFault
	 */
	function mirrorWsTest(AcceptanceTester $I) {
		// int org_id, bool sub_orgs, bool recursive, int client_id, string shared_secret
		$params = ['a'=>13];
		$wsdl = 'http://localhost:8080/api/soap';
		$method = 'mirror';
		$p = $method.'Result';

		$client = new \SoapClient($wsdl, [
			'cache_wsdl'=>WSDL_CACHE_NONE,
			'cache_wsdl_ttl'=>0,
		]);

		$soapResult = $client->__soapCall($method, ['parameters'=>$params], ['exceptions' => 1]);

		$I->assertFalse(is_soap_fault($soapResult));

		$I->assertTrue(isset($soapResult->$p));
		$xml = simplexml_load_string($soapResult->$p);
		$expected = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<ROOT xmlns="">
  <SERVER_INFO CLIENT_IP="127.0.0.1" CLIENT_ID="department"/>
  <ORG ORG_ID="1" ORG_NAME="Egyetemi polgárok szervezetei" IS_DELETED="False" TYPE="0" NEXON_KOD="" PARENT_ID="0" FULL_PATH="Pécsi Tudományegyetem / Egyetemi polgárok szervezetei" K2_ID="0"/>
</ROOT>

EOT;
		$I->assertEquals(str_replace("\r\n", "\n", $expected), $xml->asXML());

	}
}
