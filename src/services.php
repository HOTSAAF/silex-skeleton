<?php

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\HttpFoundation\Request;

use App\Service\ApiService;
use App\Service\GoodToKnowService;
use App\Service\FlashBagService;
use App\Service\TranslatedStringExposerService;
use App\Service\FormService;
use App\Validator\Constraints\VerifiedReCaptchaValidator;
use App\Service\UrlStoreService;
use App\Service\MobileDetectService;
use App\Service\MaintenanceService;

// use App\Form\Extensions\ManagerRegistry;

use Saxulum\DoctrineOrmManagerRegistry\Doctrine\ManagerRegistry;
use ZeeCoder\MailChimp\Helper as MailChimpHelper;

// $app['form.extensions'] = $app->extend('form.extensions', function ($extensions) use ($app) {
//     // $managerRegistry = new ManagerRegistry(null, array(), array('orm.em'), null, null, $app['orm.proxies_namespace']);
//     // $managerRegistry = new ManagerRegistry(null, array(), array('orm.em'), null, null, '\Doctrine\ORM\Proxy\Proxy');
//     $managerRegistry = new ManagerRegistry($app);
//     // $managerRegistry->setContainer($app);
//     $extensions[] = new DoctrineOrmExtension($managerRegistry);

//     return $extensions;
// });

// Defining a custom route class, so that the SecurityTrait can be added.
// http://silex.sensiolabs.org/doc/usage.html#traits
$app['route_class'] = 'App\\Route';

$app['validator.recaptcha'] = function() use ($app) {
    return new VerifiedReCaptchaValidator(
        $app['config']['app']['recaptcha'],
        $app['request_stack']
    );
};

$app['service.api'] = function() use ($app) {
    return new ApiService($app['request_stack']);
};

$app['service.form'] = function() use ($app) {
    return new FormService(
        $app['form.factory'],
        $app['url_generator'],
        $app['config']['app']['recaptcha'],
        $app['request_stack']
    );
};

$app['service.mailchimp'] = function() use ($app) {
    return new MailChimpHelper($app['config']['mailchimp_helper']);
};

$app['service.good_to_know'] = function() use ($app) {
    return new GoodToKnowService(
        __DIR__ . '/config/good_to_know.yml',
        $app['translator'],
        $app['request_stack']
    );
};

$app['service.maintenance'] = function() use ($app) {
    return new MaintenanceService(
        __DIR__ . '/..'
    );
};

$app['service.translator_string_exposer'] = function() use ($app) {
    return new TranslatedStringExposerService($app['translator'], $app['request_stack'], __DIR__ . '/config/trans_expose.yml');
};

$app['service.flashbag'] = function() use ($app) {
    return new FlashBagService($app['session']);
};

$app['service.url_store'] = function() use ($app) {
    return new UrlStoreService(
        $app['session'],
        $app['request_stack'],
        $app['url_generator']
    );
};

$app['mobile_detect_lib'] = function() {
    return new \Mobile_Detect();
};

$app['service.mobile_detect'] = function() use ($app) {
    return new MobileDetectService(
        $app['mobile_detect_lib'],
        [
            'isMobile',
            'isTablet',
            'isiOS',
            'isAndroidOS',
        ]
    );
};

$app['translator'] = $app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    $localeDirFinder = new Finder();
    $localeDirFinder->in(__DIR__ . '/translations')->depth(0);

    foreach ($localeDirFinder as $localeDir) {
        $locale = basename($localeDir);

        $localeDir = __DIR__ . '/translations/'.$locale;

        $translationFinder = new Finder();
        $translationFinder->in($localeDir);

        foreach ($translationFinder as $translationFile) {
            list($translationDomain, $extension) = explode('.', $translationFile->getFilename());
            $translator->addResource('yaml', $translationFile->getRealpath(), $locale, $translationDomain);
        }
    }

    return $translator;
});

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset, $versionOmitted = false) use ($app) {
        return $app['request_stack']->getMasterRequest()->getBasepath() . '/' . $asset . ($versionOmitted ? '' : '?v' . $app['config']['asset_version']);
    }));

    // $twig->addTest(new Twig_SimpleTest('DateTime', function ($date) {
    //     return $date instanceof \DateTime;
    // }));

    // DISCONTINUED: too much work would be done in the view
    // $twig->addFilter(new Twig_SimpleFilter('repair', function ($string) use ($app) {
    //     $tidy = new \Tidy();

    //     return $tidy->repairString($string, [
    //         'show-body-only' => true,
    //         'wrap' => 0,
    //     ], 'utf8');
    // }));

    $twig->addFilter(new Twig_SimpleFilter('shorten', function ($string, $maxLength) use ($app) {
        if (strlen($string) <= $maxLength) {
            return $string;
        }

        return substr($string, 0, $maxLength) . '...';
    }));

    return $twig;
});

// Monolog Slack handler
$app['monolog'] = $app->extend('monolog', function ($monolog, $app) {
    if (!$app['debug']) {
        // $token,
        // $channel,
        // $username = 'Monolog',
        // $useAttachment = true,
        // $iconEmoji = null,
        // $level = Logger::CRITICAL,
        // $bubble = true,
        // $useShortAttachment = false,
        // $includeContextAndExtra = false

        $slackHandler = new \Monolog\Handler\SlackHandler(
            $app['config']['monolog']['slack_handler']['token'], // token
            $app['config']['monolog']['slack_handler']['channel'], // channel
            null, // username (auto-detected by the token)
            true, // useAttachment
            'heavy_exclamation_mark', // iconEmoji
            \Monolog\Logger::CRITICAL, // level
            true, //
            true, // useShortAttachment
            true // includeContextAndExtra
        );

        $slackHandler->pushProcessor(function($record) use ($app) {
            $request = Request::createFromGlobals();
            $record['context']['site'] = $app['config']['app']['name'];
            $record['context']['domain'] = $request->getHttpHost();

            return $record;
        });

        $monolog->pushHandler($slackHandler);
    }

    return $monolog;
});

// Monolog Swift Mail handler
// $app['monolog'] = $app->extend('monolog', function($monolog, $app) {
//     if (!$app['debug']) {
//         $request = Request::createFromGlobals();

//         $message =
//             \Swift_Message::newInstance(
//                 'CRITICAL ERROR - ' .
//                 $app['config']['app']['name'] .
//                 ' (' . $request->getSchemeAndHttpHost() . ')'
//             )
//             ->setFrom([$app['config']['mail']['swift_options']['username'] => $app['config']['app']['name']])
//             ->setTo([$app['config']['monolog']['swift_mailer_handler']['critical_error_notification_to']])
//         ;

//         $monolog->pushHandler(new \Monolog\Handler\SwiftMailerHandler(
//             $app['mailer'],
//             $message,
//             \Monolog\Logger::CRITICAL
//         ));
//     }

//     return $monolog;
// });
