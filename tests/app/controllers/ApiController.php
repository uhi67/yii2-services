<?php /** @noinspection PhpUnused */

/**
 * @link https://github.com/uhi67/yii2-services
 * @license https://github.com/uhi67/yii2-services/blob/master/LICENSE.md
 */

namespace uhi67\services\tests\app\controllers;

use uhi67\services\WebServiceAction;
use uhi67\services\WsdlGenerator;
use yii\web\Controller;

/**
 * Class ApiController
 *
 * Endpoint: http://localhost:8080/api?ws=1
 * WSDL: http://localhost:8080/api
 * Target namespace: urn:uhi67/services/tests/app/controllers/ApiControllerwsdl
 */
class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @noinspection PhpUndefinedClassInspection */
        return [
            'soap' => [
                'class' => WebServiceAction::class,
	            'serviceUrl' => 'http://localhost:8080/api?ws=1',
	            'wsdlUrl' => 'http://localhost:8080/api',
	            'serviceOptions' => [
		            'generatorConfig' =>[
		            	'class' => WsdlGenerator::class,
					    'operationBodyStyle' => [
						    'use' => WsdlGenerator::USE_LITERAL,
						    'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
					    ],
		            ]
	            ],
                'classMap' => [
                    'SoapModel' => uhi67\services\tests\app\models\SoapModel::class,
                ],
            ],
	        'index' => [
		        'class' => WebServiceAction::class,
	        ]
        ];
    }
    /**
     * @param uhi67\services\tests\app\models\SoapModel $myClass
     * @return string
     * @soap
     * @noinspection PhpUndefinedNamespaceInspection
     * @noinspection PhpUndefinedClassInspection
     */
    public function soapTest($a)
    {
        return get_class($a);
    }

	/**
	 * @param array $params
	 * @return \stdClass
	 * @soap
	 */
	public function mirror($params)
	{
		throw new \Exception('mirror='.print_r($params,true));
		$object = new \stdClass();
		foreach($params as $key=>$value) $object->$key = $value;
		return $object;
	}

	public function actionHello() {
		return 'Hello World';
	}
}
