<?php

use Symfony\Component\Debug\Debug;

use App\Util\AppUtility;

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || (
        !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1')) &&
        // A simple check, whether the client's ip is in the same subnet as the
        // server.
        filter_var(@$_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)
    )
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

AppUtility::setEnv('dev');
$app = require __DIR__.'/../src/app.php';
$app->run();
