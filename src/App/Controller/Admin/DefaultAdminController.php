<?php

namespace App\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Admin\AdminController;

class DefaultAdminController extends AdminController
{
    public function indexAction(Request $request, Application $app)
    {
        // Filter form
        $filter_form = $this->createFilterForm($request, $app['form.factory'], $app['url_generator']);

        $app['service.flashbag']->addSuccess($app['translator']->trans('success_msg', [], "admin_flash-bag"), 'admin_home');
        $app['service.flashbag']->addError($app['translator']->trans('error_msg', [], "admin_flash-bag"), 'admin_home');
        $app['service.flashbag']->addWarning($app['translator']->trans('warning_msg', [], "admin_flash-bag"), 'admin_home');
        $app['service.flashbag']->addInfo($app['translator']->trans('info_msg', [], "admin_flash-bag"), 'admin_home');

        return $app['twig']->render('admin/home.html.twig', array(
            'filter_form' => $filter_form->createView(),
        ));
    }

    public function loginAction(Request $request, Application $app)
    {
        return $app['twig']->render('admin/login.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }
}
