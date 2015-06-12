<?php

namespace App\Controller;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

use App\Exception\ApiException;
use App\Util\AppUtility;

class ApiController
{
    // public function __construct()
    // {
    // }

    public function indexAction(Request $request, Application $app)
    {
        // This will trigger an "unknown error" on the client side in the
        // api_ajax module, since the resulting response will not be a valid
        // JSON string.
        // echo 'trigger error';

        // This will trigger an API error, and monolog will log it as "CRITICAL".
        // Critical errors are handled by a specific handler. (Ex: Slack)
        // Exceptions that are not "ApiExceptions" will be handled based on the
        // app['debug'] value. If the app runs in debug mode, the response
        // code will be 200, and the exception message will be given as the
        // "response.message". If the app runs in non-debug mode, an
        // "unknown error" text will be shown with an 500 error code, and it
        // will trigger the above mentioned handler.
        // throw new \Exception('asd asd asd ');

        // ApiExceptions are normally considered "safe" to show to the user.
        // These will not trigger critical monolog handlers, and they will have
        // a normal 200 error code. (The JSON response status will be "error"
        // hovewer.)
        // throw new ApiException('A "safe" API exception that can be shown to the user.');

        // If an ApiException's second argumetn is "true", it is considered
        // "critical". As a critical exception, it will be handled as any other
        // Excpetion type would be handled.
        // throw new ApiException('A manually triggered, critical API exception', true);

        return $app['service.api']->getResponse('Hi. :) Welcome to API v' . $request->get('version') . '.');
    }

    public function sendContactMailAction(Request $request, Application $app)
    {
        $contactForm = $app['service.form']->getContactForm();
        $contactForm->handleRequest($request);

        // sleep(1); // Simulating a longer request.

        // throw new \Exception("Error Processing Request", 1);
        // return $app['service.api']->getResponse('mail sent', false);

        if ($contactForm->isValid()) {
            $message =
                \Swift_Message::newInstance()
                ->setFrom([$app['config']['mail']['swift_options']['username'] => $app['config']['app']['name']])
                ->setTo([$app['config']['app']['contact_form_target'] => 'Contact Form Target'])
                ->setSubject($app['config']['app']['name'] . ' - Contact Form Message')
                ->setBody(
                    $app['twig']->render(
                        'email/contact_form_email.html.twig',
                        [
                            'contact_form' => $contactForm,
                            'date' => new \DateTime('now'),
                        ]
                    ),
                    'text/html'
                )
                // ->addPart($plainBody, 'text/plain') // Can be used to include a plain text version
            ;

            // Since the "swiftmailer.use_spool" was registered as "false" in
            // the service provider, e-mail sending will happen in real-time,
            // instead of spooling it and sending it later.
            // If spooling were set to "true", then exceptions would happen
            // after the handling of the requests, when the kernel terminates:
            // http://stackoverflow.com/a/26319673
            // which means, that the error won't be caught by the error() handler.
            // Solutions to this problem is to either switch off spooling
            // altogether, or handling the exception when the TERMINATE kernel
            // event is triggered. (Needs an EventListener implementation.)
            if (!$app['mailer']->send($message, $errors)) {
                throw new \Exception('');
            }

            // With spooling switched on, either a custom exception handling
            // would be needed, or a custom logic inside the EXCEPTION handler.
            // I didn't test this however. :/
            // Event Listener docs: http://symfony.com/doc/current/cookbook/service_container/event_listener.html
            // $app['dispatcher']->addListener(KernelEvents::TERMINATE, function() { });
            // $app['dispatcher']->addListener(KernelEvents::EXCEPTION, function() { });

            return $app['service.api']->getResponse($app['translator']->trans('api_success', [], 'contact_form'));
        }

        return $app['service.api']->getResponse(
            AppUtility::getFormErrorsForApi($contactForm),
            false
        );
    }

    public function subscribeToMailChimpAction(Request $request, Application $app)
    {
        $parameters = $app['service.api']->getMandatoryPostParameters(['email']);

        $mailChimpResponse = $app['service.mailchimp']->subscribeToList(
            $app['config']['mailchimp']['list_id'],
            $parameters['email']
        );

        if (
            !empty($mailChimpResponse['status']) &&
            $mailChimpResponse['status'] == 'error'
        ) {
            $errorMessage = $app['translator']->trans($mailChimpResponse['code'], [], 'mailchimp_errors');

            if ($errorMessage !== (string) $mailChimpResponse['code']) {
                throw new ApiException($errorMessage);
            }

            throw new ApiException($mailChimpResponse['error'], true);
        } else if (!$mailChimpResponse) {
            throw new ApiException('MailChimp connection error!', true);
        }

        return $app['service.api']->getResponse();
    }

    public function authTestAction(Request $request, Application $app)
    {
        return $app['service.api']->getResponse([
            "description" => "This API call checks what kind of authentication do you have.",
            [
                [
                    "Are you logged in?",
                    ($app['security']->isGranted('IS_AUTHENTICATED_ANONYMOUSLY') ? 'yep' : 'nope')
                ],
                [
                    "Are you logged in \"fully authenticated\"? (Meaning that you're logged in, but not as anonymous.)",
                    ($app['security']->isGranted('IS_AUTHENTICATED_FULLY') ? 'yep' : 'nope')
                ],
                [
                    "Are you logged in as ADMIN?",
                    ($app['security']->isGranted('ROLE_ADMIN') ? 'yep' : 'nope')
                ],
            ]
        ]);
    }

    public function adminCallTestAction(Request $request, Application $app)
    {
        // Gets the User object. (The method is provided by the SecurityTrait.)
        // By default it is "Symfony\Component\Security\Core\User\User"
        $User = $app->user();

        return $app['service.api']->getResponse([
            "description" => "This call is safeguarded, only users with the \"ROLE_ADMIN\" role can access it.",
            "user_data" => [
                "username" => $User->getUsername(),
                "enabled" => $User->isEnabled(),
                "accountNonExpired" => $User->isAccountNonExpired(),
                "credentialsNonExpired" => $User->isCredentialsNonExpired(),
                "accountNonLocked" => $User->isAccountNonLocked(),
                "roles" => $User->getRoles(),
            ],
        ]);
    }

    public function setOrderGalleryImagesAction(Request $request, Application $app) {
        $em = $app['doctrine']->getManager();
        $repo = $em->getRepository('\\App\\Entity\\GalleryImage');

        $order = $request->get('sort', []);
        foreach ($order as $k => $v) {
            try {
                $Entity = $repo->findOneById($v);
                $Entity->setOrder($k+1);
                $em->persist($Entity);
                $em->flush();
            } catch (\Exception $e) {
                throw new ApiException('Order updating error!');
            }
        }

        return $app['service.api']->getResponse('Done');
    }
}
