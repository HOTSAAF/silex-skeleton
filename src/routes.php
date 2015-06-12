<?php

use App\Util\AppUtility;

// Application Routes
$app->get('/', 'App\\Controller\\HomeController::indexAction')->bind('home');
$app->post('/contact_send', 'App\\Controller\\HomeController::contactAction')->bind('contact_send');

// Login
$app->get('/login', 'App\\Controller\\Admin\\DefaultAdminController::loginAction')->bind('login');

// Admin Routes
$controllerFactory = $app['controllers_factory'];

    $controllerFactory
        ->get('/', 'App\\Controller\\Admin\\DefaultAdminController::indexAction')
        ->bind('admin_home')
    ;

    $controllerFactory
        ->match('/article', 'App\\Controller\\Admin\\ArticleAdminController::indexAction')
        ->bind('admin_article_index')
    ;

    $controllerFactory
        ->match('article/remove/{id}', 'App\\Controller\\Admin\\ArticleAdminController::removeAction')
        ->bind('admin_article_remove')
    ;

    $controllerFactory
        ->match('/article/{id}/edit', 'App\\Controller\\Admin\\ArticleAdminController::editAction')
        ->bind('admin_article_edit')
    ;

    $controllerFactory
        ->match('/article/new', 'App\\Controller\\Admin\\ArticleAdminController::newAction')
        ->bind('admin_article_new')
    ;

    // Galery
    $controllerFactory
        ->get('/gallery', 'App\\Controller\\Admin\\GalleryAdminController::indexAction')
        ->bind('admin_gallery_index')
    ;

    $controllerFactory
        ->match('/gallery/new', 'App\\Controller\\Admin\\GalleryAdminController::newAction')
        ->bind('admin_gallery_new')
    ;

    $controllerFactory
        ->get('/gallery/{id}/remove', 'App\\Controller\\Admin\\GalleryAdminController::removeAction')
        ->bind('admin_gallery_remove')
    ;

$app->mount('/admin', $controllerFactory);

// Api routes
$controllerFactory = $app['controllers_factory'];
    $controllerFactory
        ->get('/', 'App\\Controller\\ApiController::indexAction')
        ->bind('api_index')
    ;
    $controllerFactory
        ->post('/sortable', 'App\\Controller\\ApiController::setOrderGalleryImagesAction')
        ->bind('api_sortable_galery_image')
    ;
    $controllerFactory
        ->post('/subscribe_to_mailchimp', 'App\\Controller\\ApiController::subscribeToMailChimpAction')
        ->bind('api_subscribe_to_mailchimp')
    ;
    $controllerFactory
        ->post('/send_contact_mail', 'App\\Controller\\ApiController::sendContactMailAction')
        ->bind('api_send_contact_mail')
    ;
    $controllerFactory
        ->get('/auth_test', 'App\\Controller\\ApiController::authTestAction')
        ->bind('api_auth_test')
    ;
    $controllerFactory
        ->get('/admin_call_test', 'App\\Controller\\ApiController::adminCallTestAction')
        ->secure('ROLE_ADMIN')
        ->bind('api_admin_call_test')
    ;
$app->mount('/api/{version}', $controllerFactory);

// Test routes for the DEV environment
if (AppUtility::getEnv() === 'dev') {
    $controllerFactory = $app['controllers_factory'];
        $controllerFactory->get('/contact-form', 'App\\Controller\\TestController::contactFormAction');
        $controllerFactory->get('/paralax', 'App\\Controller\\TestController::paralaxAction');
    $app->mount('/test', $controllerFactory);
}
