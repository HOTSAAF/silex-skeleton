<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use \Silex\EventListener\LogListener;

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use App\Util\AppUtility;
use App\Exception\ApiException;

use Saxulum\DoctrineOrmManagerRegistry\Provider\DoctrineOrmManagerRegistryProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Psr\Log\LogLevel;

$app->register(new SecurityServiceProvider(),
    require __DIR__ . '/config/security.php'
);

$app->register(new FormServiceProvider());
$app->register(new RoutingServiceProvider());
$app->register(new ValidatorServiceProvider(), [
    'validator.validator_service_ids' => [
        'validator.recaptcha' => 'validator.recaptcha',
    ],
]);
$app->register(new ServiceControllerServiceProvider());

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/views',
    'twig.options' => [
        'cache' => __DIR__ . '/../var/cache/twig'
    ],
    'twig.form.templates' => [
        'form/form_div_layout.html.twig', // The default must be provided too.
        'form/recaptcha.html.twig',
    ],
]);

$app->register(new HttpFragmentServiceProvider());
$app->register(new SessionServiceProvider());

$app->register(new TranslationServiceProvider(), [
    // This is the default locale. This will change according to the current
    // request locale.
    'locale' => $app['config']['locales'][0],
    'locale_fallbacks' => $app['config']['locales'],
]);

$app->register(new SwiftmailerServiceProvider(), [
    'swiftmailer.options' => $app['config']['mail']['swift_options'],
    'swiftmailer.use_spool' => $app['config']['mail']['swift_use_spool'],
]);

if (AppUtility::getEnv() == 'dev') {
    $app->register(new WebProfilerServiceProvider(), [
        'profiler.cache_dir' => __DIR__.'/../../var/cache/profiler',
    ]);
}

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => __DIR__.'/../var/logs/silex_'.AppUtility::getEnv().'.log',
    'monolog.name' => $app['config']['app']['name'],
    'monolog.listener' => function() use ($app) {
        return new LogListener($app['logger'], function (\Exception $e) {
            if (
                // Simple http exceptions should only count as "error" level logs
                ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) ||
                // Simple API exceptions count as "ERROR" types.
                // Critical ones are "CRITICAL" ofc.
                ($e instanceof ApiException && !$e->isCritical())
            ) {
                return LogLevel::ERROR;
            }

            return LogLevel::CRITICAL;
        });
    },
]);

$app->register(new DoctrineServiceProvider(), [
    'dbs.options' => [
        'pdo_mysql' => $app['config']['db'],
    ],
]);

// This "hack" was needed to enable advanced ORM features, like the lifecycle
// callbacks (pre/post etc)
$loader = require __DIR__ . '/../vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$doctrineCache = [
    'driver' =>  $app['debug'] ? 'array': 'filesystem',
    'path' => __DIR__.'/../var/cache/doctrine/cache'
];

$app->register(new DoctrineOrmServiceProvider, [
    'orm.proxies_dir' => __DIR__.'/../var/cache/doctrine/proxies',
    'orm.default_cache' => $doctrineCache,
    "orm.ems.options" => [
        'dbs' => [
            'connection' => 'pdo_mysql',
            "mappings" => [
                [
                    "type" => "annotation",
                    'use_simple_annotation_reader' => false,
                    "namespace" => "App\Entity",
                    "path" => __DIR__.'/App/Entity',
                ],
            ],
        ],
    ],
    // 'orm.auto_generate_proxies' => true,
]);

$app->register(new DoctrineOrmManagerRegistryProvider());
