<?php
$environment = 'local';
error_reporting(E_ALL);
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'local');
defined('YII_ENV_DEV') or define('YII_ENV_DEV', true);

require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
require dirname(dirname(dirname(__DIR__))) . '/vendor/yiisoft/yii2/Yii.php';

$config = require dirname(__DIR__) . '/config/test-config.php';

(new yii\web\Application($config))->run();
