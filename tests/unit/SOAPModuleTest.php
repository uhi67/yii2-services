<?php
namespace unit;

use Codeception\Module\SOAP;
use Codeception\Test\Unit;
use UnitTester;

class SOAPModuleTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests

    /**
     * @dataProvider provSoapEncode
     * @param string $expected
     * @param mixed $value
     */
    public function testSoapEncode($expected, $value) {
        if(is_array($value) && isset($value[0]) && preg_match('~^{([^}]+)}$~', $value[0], $mm)) {
            array_shift($value);
            $className = $mm[1];
            $value = new $className(...$value);
        }
        $encoded = SOAP::soapEncode($value);
        /** @noinspection PhpParamsInspection */
        $this->tester->assertXmlStringEqualsXmlString($expected, $encoded);
    }

    function provSoapEncode() {
        return [
            [/** @lang */'<item xsi:type="xsd:int" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">13</item>',
                13],
            [/** @lang */'<item xsi:type="SOAP-ENC:Struct" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                            <a xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENC:arrayType="xsd:int[3]" xsi:type="SOAP-ENC:Array">
                                <item xsi:type="xsd:int">13</item>
                                <item xsi:type="xsd:int">14</item>
                                <item xsi:type="xsd:int">15</item>
                            </a>
                        </item>',
                ['a'=>[13, 14, 15]]],
            [/** @lang */'<item xsi:type="SOAP-ENC:Struct" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                            <a xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENC:arrayType="xsd:anyType[3]" xsi:type="SOAP-ENC:Array">
                                <item xsi:type="xsd:int">13</item>
                                <item xsi:type="xsd:boolean">true</item>
                                <item xsi:type="xsd:string">foo</item>
                            </a>
                        </item>',
                ['a'=>[13, true, 'foo']]],
            [/** @lang */'<item xsi:type="xsd:dateTime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">2004-04-12T13:20:00-05:00</item>', ['{\DateTime}',
                '2004-04-12T13:20:00-05:00']],
        ];
    }
}
