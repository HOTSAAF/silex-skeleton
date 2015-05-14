<?php

use App\Utils\AppUtility;

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

AppUtility::setEnv('prod');
$app = require __DIR__.'/../src/app.php';
$app->run();
