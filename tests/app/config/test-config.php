<?php

return [
    'id' => 'test-api',
    'name' => 'test-api',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(dirname(__DIR__))) . '/vendor',
    'controllerNamespace' => 'uhi67\services\tests\app\controllers',
    'components' => [
        'urlManager' => [
        	'baseUrl' => ':8080/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
];
