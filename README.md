SOAP Web Service for Yii2 framework
===================================

## Description

WebService encapsulates SoapServer and provides a WSDL-based web service.
Adaptation of Yii1 Web Services

Based on work of Qiang Xue <qiang.xue@gmail.com> and Andrey Borodulin

Change log is below

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

To install, either run

```
$ php composer.phar require uhi67/yii2-soap-server "^1.5"
```
or add

```
"uhi67/yii2-soap-server": "^1.5"
```

to the ```require``` section of your `composer.json` file.

## Usage

```php
namespace app\controllers;

class SiteController extends \yii\web\Controller
{
    public function actions()
    {
        return [
            'soap' => [
                'class' => 'conquer\services\WebServiceAction',
                'classMap' => [
                    'MyClass' => 'app\controllers\MyClass'
                ],
            ],
        ];
    }
    /**
     * @param \app\controllers\MyClass $myClass
     * @return string
     * @soap
     */
    public function soapTest($myClass)
    {
        return get_class($myClass);
    }
}

/**
* Class MyClass
 * @soap
 */
class MyClass
{
    /**
     * @var string
     * @soap
     */
    public $name;
}
```

## Testing

1. Before testing, run once `composer install` from repository root.
2. Run `php tests/app/yii serve` if you're going to run acceptance tests.  
3. Run `codecept run` from repository root.

## License

**uhi67/yii2-soap-server** is released under the BSD License. See the bundled `LICENSE.md` for details.

## Change log

### 1.5

- corrections for codeception functional testing and some test examples
- online API documentation with `?doc` query 

