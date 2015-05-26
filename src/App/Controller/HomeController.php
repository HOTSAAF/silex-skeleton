<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class HomeController
{
    public function indexAction(Request $request, Application $app)
    {
        return $app['twig']->render('home.html.twig', [
            'contact_form' => $app['service.form']->getContactForm()->createView(),
            'good_to_know' => $app['service.good_to_know']->getAllByGroup('non_admin'),
        ]);
    }
}
