<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(__DIR__) . '/app/');

require_once dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

ini_set('soap.wsdl_cache_enabled', 0);
