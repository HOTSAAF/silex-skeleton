<?php

namespace App\Controller\Admin\Article;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Article;
use App\Controller\Admin\AdminController;
use App\Exception\NotFoundException;

class ArticleAdminController extends AdminController
{
    public function indexAction(Request $request, Application $app)
    {
        // Save this url
        $app['service.url_store']->saveUrlByKey('admin_article_index');

        // Filter form
        $filter_form = $this->createFilterForm($request, $app['form.factory'], $app['url_generator'], 'admin_article_index');

        $paginator = $this->getPaginatedResultByEntity($request, $app, 'Article', "admin_article_index");

        // Redirect last page, if current page is empty
        if ($paginator->count() == 0) {

            $totalPageNumber = ceil($paginator->getTotalItemCount()/$app['config']['admin']['result_page_size']);
            $parameters = $app['service.url_store']->getQueryByKey('admin_article_index');

            if (isset($parameters['page'])) {
                $parameters['page'] = $totalPageNumber;
            }

            return $app->redirect($app['url_generator']->generate('admin_article_index', $parameters));
        }

        return $app['twig']->render('admin/article/list.html.twig', array(
            'filter_form' => $filter_form->createView(),
            'paginator' => $paginator
        ));
    }

    public function newAction(Request $request, Application $app) {
        // $em = $app['doctrine']->getManager();

        return $app->redirect($app['service.url_store']->getUrlByKey('admin_article_index'));
    }

    public function editAction(Request $request, Application $app, $id) {
        // $em = $app['doctrine']->getManager();
        // $Entity = $this->checkIfEntityExistsById($em, 'Article', $id);

        return $app->redirect($app['service.url_store']->getUrlByKey('admin_article_index'));
    }

    public function removeAction(Request $request, Application $app, $id) {
        $em = $app['doctrine']->getManager();

        try {

            $Entity = $this->checkIfEntityExistsById($em, 'Article', $id);
            $em->remove($Entity);
            $em->flush();
            $app['service.flashbag']->addSuccess(
                $app['translator']->trans('success_msg', [], "admin_flash-bag"),
                'admin_article_index'
            );

        } catch (NotFoundException $ex) {
            $app['service.flashbag']->addError($ex->getMessage(), 'admin_article_index');
        }

        return $app->redirect($app['service.url_store']->getUrlByKey('admin_article_index'));
    }
}
