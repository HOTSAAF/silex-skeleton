<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * This controller contains all the "virtual routes" that can be rendered by the
 * twig `{{ render(controller('')) }}` call.
 */
class RenderController
{
    public function mdClasses(Request $request, Application $app)
    {
        return new Response(implode(' ', $app['service.mobile_detect']->getHtmlClasses()));
    }

    public function maintenanceMode(Request $request, Application $app)
    {
        return $app['twig']->render('includes/maintenance_mode.html.twig', [
            'maintenance_mode' => $app['service.maintenance']->isMaintenanceMode(),
        ]);
    }

    public function exposeDataToClient(Request $request, Application $app, $transGroup = null)
    {
        $config = [
            'util' => [
                'basepath' => $app['request_stack']->getCurrentRequest()->getBasepath(),
                'baseurl' => rtrim($app['url_generator']->generate('home'), '/'),
                'locale' => $request->getLocale(),
                'asset_version' => $app['config']['asset_version'],
                'api_versions' => json_encode($app['config']['api_versions']),
                'current_api_version' => $app['config']['api_versions'][count($app['config']['api_versions']) - 1],
            ],
            'trans' => $app['service.translator_string_exposer']->getExposedCollection($transGroup),
            'ztrans' => [
                $request->getLocale() => $app['service.translator_string_exposer']->getExposedCollection($transGroup),
            ],
            'contact_form' => [
                'recaptcha_site_key' => $app['config']['app']['recaptcha']['site_key'],
            ],
        ];

        return new Response('<script id="js-data-loader" type="exposed-data/json">' . json_encode($config) . '</script>');
    }

    public function adminGoodToKnow(Request $request, Application $app)
    {
        $tips = $app['service.good_to_know']->getAllByGroup($app['request_stack']->getMasterRequest()->attributes->get('_route'));

        return $app['twig']->render('admin/includes/_good_to_know.html.twig', array(
            'tips' => $tips,
        ));
    }

    public function loginGoodToKnow(Request $request, Application $app)
    {
        $tips = $app['service.good_to_know']->getAllByGroup('login_page');

        return $app['twig']->render('admin/includes/_good_to_know.html.twig', array(
            'title' => $app['translator']->trans('login_warning_title', [], 'admin_login'),
            'tips' => $tips,
        ));
    }
}
