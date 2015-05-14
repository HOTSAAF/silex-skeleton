<?php

use Symfony\Component\HttpFoundation\Request;
// use Silex\Application;
use App\Application;
use App\Util\AppUtility;

date_default_timezone_set('Europe/Budapest');

Request::enableHttpMethodParameterOverride();

$app = new Application();

AppUtility::loadAppConfiguration($app);
AppUtility::loadEnvAppConfiguration($app);

require 'service_providers.php';
require 'services.php';
require 'handlers.php';
require 'routes.php';

return $app;
