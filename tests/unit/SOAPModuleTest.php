<?php
namespace unit;

use Codeception\Module\SOAP;
use Codeception\Test\Unit;

class SOAPModuleTest extends Unit
{
    /**
     * @var \UnitTester
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
        if(is_array($value) && isset($value[0]) && preg_match('~^{([_\\\w]+)}$~', $value[0], $mm)) {
            array_shift($value);
            $value = call_user_func_array([$mm[1], '__construct'], $value);
        }
        $encoded = SOAP::soapEncode($value);
        $this->tester->assertXmlStringEqualsXmlString($expected, $encoded);
    }

    function provSoapEncode() {
        return [
            [/** @lang */'<item xsi:type="xsd:int" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">13</item>',
                13],
            [/** @lang */'<item xsi:type="SOAP-ENC:Struct" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                            <a SOAP-ENC:arrayType="xsd:string[3]" xsi:type="SOAP-ENC:Array">
                                <item xsi:type="xsd:int">13</item>
                                <item xsi:type="xsd:boolean">true</item>
                                <item xsi:type="xsd:string">citrom</item>
                            </a>
                        </item>',
                ['a'=>[13, true, 'foo']]],
            ['<item xsi:type="xsd:dateTime">2004-04-12T13:20:00-05:00</item>', ['{\DateTime}',
                '2004-04-12T13:20:00-05:00']],
        ];
    }
}
