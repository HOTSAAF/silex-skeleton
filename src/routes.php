<?php

use App\Util\AppUtility;

// Application Routes
$app->get('/', 'App\\Controller\\HomeController::indexAction')->bind('home');
$app->post('/contact_send', 'App\\Controller\\HomeController::contactAction')->bind('contact_send');

// Login
$app->get('/login', 'App\\Controller\\Admin\\DefaultAdminController::loginAction')->bind('login');

// Admin Routes
$adminControllerFactory = $app['controllers_factory'];

    $adminControllerFactory
        ->get('/', 'App\\Controller\\Admin\\DefaultAdminController::indexAction')
        ->bind('admin_home')
    ;

    $adminControllerFactory
        ->match('/article', 'App\\Controller\\Admin\\Article\\ArticleAdminController::indexAction')
        ->bind('admin_article_index')
    ;

    $adminControllerFactory
        ->match('article/remove/{id}', 'App\\Controller\\Admin\\Article\\ArticleAdminController::removeAction')
        ->bind('admin_article_remove')
    ;

    // $adminControllerFactory
    //     ->match('/test/article/{id}/edit', 'App\\Controller\\Admin\\ArticleTestAdminController::editArticleAction')
    //     ->bind('test_new_article')
    // ;

    // $adminControllerFactory
    //     ->match('/test/article', 'App\\Controller\\Admin\\ArticleTestAdminController::indexAction')
    //     ->bind('test_list_article')
    // ;

$app->mount('/admin', $adminControllerFactory);

// Api routes
$apiControllerFactory = $app['controllers_factory'];
    $apiControllerFactory
        ->get('/', 'App\\Controller\\ApiController::indexAction')
        ->bind('api_index')
    ;
    $apiControllerFactory
        ->post('/subscribe_to_mailchimp', 'App\\Controller\\ApiController::subscribeToMailChimpAction')
        ->bind('api_subscribe_to_mailchimp')
    ;
    $apiControllerFactory
        ->post('/send_contact_mail', 'App\\Controller\\ApiController::sendContactMailAction')
        ->bind('api_send_contact_mail')
    ;
    $apiControllerFactory
        ->get('/auth_test', 'App\\Controller\\ApiController::authTestAction')
        ->bind('api_auth_test')
    ;
    $apiControllerFactory
        ->get('/admin_call_test', 'App\\Controller\\ApiController::adminCallTestAction')
        ->secure('ROLE_ADMIN')
        ->bind('api_admin_call_test')
    ;
$app->mount('/api/{version}', $apiControllerFactory);

// Test routes for the DEV environment
if (AppUtility::getEnv() === 'dev') {
    $apiControllerFactory = $app['controllers_factory'];
        $apiControllerFactory->get('/contact-form', 'App\\Controller\\TestController::contactFormAction');
        $apiControllerFactory->get('/paralax', 'App\\Controller\\TestController::paralaxAction');
    $app->mount('/test', $apiControllerFactory);
}
