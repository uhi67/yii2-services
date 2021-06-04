Web Service for Yii2 framework
=================
[![Build Status](https://travis-ci.org/uhi67/yii2-services.svg?branch=master)](https://travis-ci.org/uhi67/yii2-services)

## Description

WebService encapsulates SoapServer and provides a WSDL-based web service.
Adaptation of Yii1 Web Services

Based on work of Qiang Xue <qiang.xue@gmail.com> and Andrey Borodulin

Changes in version 1.4.2

- corrections for codeception functional testing and some test examples
- online API documentation with `?doc` query 

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

To install, either run

```
$ php composer.phar require uhi67/yii2-services "*"
```
or add

```
"uhi67/yii2-services": "*"
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

## License

**uhi67/yii2-services** is released under the BSD License. See the bundled `LICENSE.md` for details.
