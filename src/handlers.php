<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Silex\Application;

use App\Exception\ApiException;

$app->before(function (Request $request) use ($app) {
    // Allow only supported locales.
    if (!in_array($request->getLocale(), $app['config']['locales'])) {
        // Falling back to the default locale in case the request came with a
        // not supported one.
        $request->setLocale($app['config']['locales'][0]);
    }

    // Setting current app locale to the translator
    $app['locale'] = $request->getLocale();


    // Adding Twig globals
    $app['twig']->addGlobal('request', $request);

    // Making the locale "sticky".
    // if ($request->hasPreviousSession()) {
    //     if ($locale = $request->attributes->get('_locale')) {
    //         $request->getSession()->set('_locale', $locale);
    //     } else {
    //         // if no explicit locale has been set on this request, use one from the session
    //         $request->setLocale($request->getSession()->get('_locale', $app['locales'][0]));
    //     }
    // }

    // Maintenance-check
    if ($app['config']['app']['maintenance']) {
        throw new HttpException(503);
    }
});

// Adding a check for the version number when accessing the API.
$app->before(function(Request $request, Application $app) use ($app) {
    if (strpos($request->getPathInfo(), '/api') !== 0) {
        return;
    }

    $version = $request->get('version');
    if (!in_array($version, $app['config']['api_versions'])) {
        throw new ApiException("The \"$version\" version is not available in the api. The registered versions are: \"" . implode('", "', $app['config']['api_versions']) . "\"");
    }
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    // See the docs: http://silex.sensiolabs.org/doc/usage.html

    // Catching API Exceptions and anything from the "/api*" route, so that
    // they'll be properly formatted.
    if (
        // Passing the 'debugger' get variable on an api call will show the real
        // html error page in dev mode.
        $request->query->get('debugger', null) === null &&
        (
            $e instanceof ApiException ||
            strpos($request->getPathInfo(), '/api') === 0
        )
    ) {
        if (
            ($e instanceof ApiException === false) ||
            $e->isCritical()
        ) {
            if ($app['debug']) {
                // Keep the http status and original message in debug mode, but
                // keep the API response format.
                return $app['service.api']->getResponse($e->getMessage(), false);
            }

            // Return with an 500 error status, and with an "unkown error"
            // message.
            return new JsonResponse(
                $app['service.api']->getResponseData($app['translator']->trans('unexpected_error', [], 'errors'), false),
                500, /* ignored */
                ['X-Status-Code' => 500]
            );
        }

        return new JsonResponse(
            $app['service.api']->getResponseData($e->getMessage(), false),
            200, /* ignored */
            ['X-Status-Code' => 200]
        );
    }

    if ($app['debug']) {
        return;
    }

    // $locale = $app['locales'][0];

    // Missing "_locale" attribute solution.
    // $app['request_context']->setParameters(array('_locale' => $locale));
    // Translator solution.
    // $app['translator']->setLocale($locale);

    // These didn't work at all
    // $app['request']->attributes->set('_locale', $locale);
    // $request->getSession()->set('_locale', 'hu');
    // $request->setLocale('hu');
    // $app['request_context']->setLocale('en');

    // Catch all errors and show something useful.
    $templates = [
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    ];

    return new Response($app['twig']
        ->resolveTemplate($templates)
        ->render(['code' => $code]), $code);
});
