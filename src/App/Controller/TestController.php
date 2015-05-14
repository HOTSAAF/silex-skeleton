<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class TestController
{
    public function contactFormAction(Request $request, Application $app)
    {
        return $app['twig']->render('test/contact_form.html.twig', [
            'contact_form' => $app['service.form']->getContactForm()->createView(),
        ]);
    }

    public function paralaxAction(Request $request, Application $app)
    {
        return $app['twig']->render('test/paralax.html.twig');
    }
}
