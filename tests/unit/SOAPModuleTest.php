<?php
namespace unit;

use Codeception\Module\XmlAsserts;
use Codeception\Test\Unit;
use Codeception\Util\Xml;
use SoapFault;
use uhi67\soapHelper\SoapClientDry;
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
     * @param $method
     * @param $parameters
     * @throws SoapFault
     */
    public function testSoapEncode($expected, $method, $parameters) {
        if(is_array($parameters)) {
            foreach($parameters as &$value) {
                if (is_array($value) && isset($value[0]) && is_string($value[0]) && preg_match('~^{([^}]+)}$~', $value[0], $mm)) {
                    array_shift($value);
                    $className = $mm[1];
                    $value = new $className(...$value);
                }
            }
        }
        codecept_debug($parameters);

        $request = SoapClientDry::__requestXml($parameters, $method, dirname(__DIR__).'/_data/sample-wsdl.xml', null, true);
        $p = XmlAsserts::xmlQuery('//SOAP-ENV:Body', $request, ['SOAP-ENV'=>"http://schemas.xmlsoap.org/soap/envelope/"]);
        $this->assertTrue($p && $p->length>0);
        $call = $p->item(0)->firstChild;
        $this->tester->assertXmlStringEqualsXmlString($expected, Xml::toXml($call)->saveXML());
    }

    function provSoapEncode() {
        return [
            [/** @lang */'<ns1:getObject xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                    <xxx xsi:type="xsd:int">13</xxx>
                </ns1:getObject>',
                'getObject',
                [13]
            ],
            [/** @lang */'<ns1:getObject xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                        <xxx SOAP-ENC:arrayType="SOAP-ENC:Array[1]" xsi:type="SOAP-ENC:Array">
                            <item SOAP-ENC:arrayType="xsd:int[3]" xsi:type="SOAP-ENC:Array">
                                <item xsi:type="xsd:int">13</item>
                                <item xsi:type="xsd:int">14</item>
                                <item xsi:type="xsd:int">15</item>
                            </item>
                        </xxx>
                    </ns1:getObject>',
                'getObject',
                [[[13, 14, 15]]]
            ],
            [/** @lang XMLs */'<ns1:getObject xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                        <xxx xsi:type="ns2:Map">
                            <item>
                                <key xsi:type="xsd:string">a</key>
                                <value SOAP-ENC:arrayType="xsd:ur-type[3]" xsi:type="SOAP-ENC:Array">
                                    <item xsi:type="xsd:int">13</item>
                                    <item xsi:type="xsd:boolean">true</item>
                                    <item xsi:type="xsd:string">foo</item>
                                </value>
                            </item>
                        </xxx>
                    </ns1:getObject>',
                'getObject',
                [['a'=>[13, true, 'foo']]]
            ],

            // Note: DateTime object does not work
//            [/** @lang XMLs */'<ns1:getObject xmlns:ns1="urn:uhi67/services/tests/app/controllers/SampleApiControllerwsdl" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
//                        <xxx xsi:type="SOAP-ENC:Struct">
//                            <date xsi:type="xsd:string">2004-04-12 13:20:00.000000</date>
//                            <timezone_type xsi:type="xsd:int">1</timezone_type>
//                            <timezone xsi:type="xsd:string">-05:00</timezone>
//                        </xxx>
//                    </ns1:getObject>',
//                'getObject',
//                [['{\DateTime}', '2004-04-12T13:20:00-05:00']]
//            ],
        ];
    }
}
