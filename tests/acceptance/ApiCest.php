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
//        $I->canSeeResponseJsonMatchesXpath('/');
    }
}
