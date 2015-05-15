<?php

// ROUTING
$url = $_SERVER['REQUEST_URI'];
$rootPath = dirname(__FILE__) . '/..';
$publicDir = 'web';
$publicPath = $rootPath . '/' . $publicDir;

$baseUrl = '/' . implode(array_intersect(
    explode('/', trim($url, '/')),
    explode('/', trim($publicPath, '/'))
), '/');

$urlParameters = explode(
    '/',
    trim(str_replace($baseUrl, '', $_SERVER['REQUEST_URI']), '/')
);

// Check whether the first "parameter" is a front controller.
if (is_file($publicDir . '/' . $urlParameters[0])) {
    unset($urlParameters[0]);
    $urlParameters = array_values($urlParameters);
}

// URL parameters now available in the $urlParameters array.
// Can be used to detect locale for example.

// The following is not needed anymore, since the whole folder is protected by
// .htaccess rules.
// Do not allow access to this file when no actual maintenance mode is triggered.
// if (!is_file(__DIR__ . '/../maintenance.on')) {
//     header('location: ' . $baseUrl);
//     exit;
//     // Another alternative could be:
//     // header('HTTP/1.0 403 Forbidden');
//     // exit('You are not allowed to access this file.');
// }

// Setting the correct error code
http_response_code(503);

// Default type
if (empty($_GET['type'])) {
    $_GET['type'] = 'html';
}

// Setting response based on the type
if ($_GET['type'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'state' => 'error',
        'response' => 'The site is currently under maintenance, please try again later.',
    ]);
} else {
    echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Maintenance</title>
        </head>
        <body>
            <h1>Maintenance</h1>
            <p>The site is currently under maintenance, please try again later.</p>
        </body>
        </html>
    ';
}
