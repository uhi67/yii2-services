<?php
namespace unit;

use Codeception\Module\XmlAsserts;
use Codeception\Test\Unit;
use Exception;
use SoapServer;
use uhi67\services\tests\app\controllers\ApiController;
use UnitTester;
use yii\base\InvalidConfigException;
use yii\console\Application;

class ApiControllerTest extends Unit
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
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function testRawSoapService()
    {
        $soapEnvScheme = "http://schemas.xmlsoap.org/soap/envelope/";
        $namespace = 'urn:uhi67/services/tests/app/controllers/ApiControllerwsdl';
        $method = 'mirror';
        $endpoint = 'http://localhost:8080/api?ws=1';
        $request = <<<EOT
<soapenv:Envelope xmlns:ns="$namespace" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
	<soapenv:Header xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"/>
	<soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
		<ns:$method>
			<param>x</param>
		</ns:$method>
	</soapenv:Body>
</soapenv:Envelope>
EOT;
        $server = new SoapServer(null, ['uri'=>$endpoint]);
        $server->setClass(ApiController::class);
        $config = require dirname(__DIR__) . '/app/config/test-config.php';
        $application = new Application($config);
        $provider = $application->createControllerByID('api');

        // Check request
        $parser = xml_parser_create("UTF-8");
        if (!xml_parse($parser, $request, true)){
            // $server->fault("500",
            throw new Exception("Cannot parse XML: ".
                xml_error_string(xml_get_error_code($parser)).
                " at line: ".xml_get_current_line_number($parser).
                ", column: ".xml_get_current_column_number($parser));
        }

        $server->setObject($provider);
        ob_start();
        $server->handle($request);
        $response = ob_get_clean();

        $xml = XmlAsserts::toXml($response);
        $this->assertNotSame(false, $xml);
        $this->tester->assertXmlMatches('//soapenv:Envelope', $xml, ['soapenv'=>$soapEnvScheme], 'jajj');
        $this->tester->assertXmlMatches('//soapenv:Body', $xml, ['soapenv'=>$soapEnvScheme], 'jajj');
        $this->tester->assertXmlMatches("//ns1:{$method}Response", $xml, ['soapenv'=>$soapEnvScheme], 'jajj');
        $this->tester->assertXmlMatches("//return[@xsi:type='xsd:string']", $xml, ['xsi'=>"http://www.w3.org/2001/XMLSchema-instance"], 'jajj');
    }
}
