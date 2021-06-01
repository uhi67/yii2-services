<?php /** @noinspection PhpUnused */

/**
 * @link https://github.com/borodulin/yii2-services
 * @license https://github.com/borodulin/yii2-services/blob/master/LICENSE.md
 */

namespace uhi67\services\tests\app\controllers;

use uhi67\services\WebServiceAction;
use yii\web\Controller;

/**
 * Class ApiController
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
                'classMap' => [
                    'SoapModel' => uhi67\services\tests\app\models\SoapModel::class,
                ],
            ],
        ];
    }
    /**
     * @param uhi67\services\tests\app\models\SoapModel $myClass
     * @return string
     * @soap
     * @noinspection PhpUndefinedNamespaceInspection
     * @noinspection PhpUndefinedClassInspection
     */
    public function soapTest($myClass)
    {
        return get_class($myClass);
    }

    public function actionIndex()
    {
        return 'ok';
    }
}
