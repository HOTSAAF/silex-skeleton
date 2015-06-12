<?php

namespace App\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Admin\AdminController;
use App\Entity\GalleryImage;

class GalleryAdminController extends AdminController
{
     public function getDefaultOrder() {
        return 'order.ASC';
    }

    public function indexAction(Request $request, Application $app)
    {
        $em = $app['doctrine']->getManager();
        $repo = $em->getRepository('\\App\\Entity\\GalleryImage');

        $gallery_collection = $repo->findAll();

        return $app['twig']->render('admin/gallery/list.html.twig', array(
            'gallery_collection' => $gallery_collection,
        ));
    }

    public function newAction(Request $request, Application $app) {
        $em = $app['doctrine']->getManager();

        $form = $app['form.factory']->create(new \App\Form\Type\GalleryFormType(), new GalleryImage());
        $form->handleRequest($request);
        $newGalleryEntity = $form->getData();

        if ($form->isValid()) {
            $em->persist($newGalleryEntity);
            $em->flush();

            $parameters = $app['service.url_store']->getQueryByKey('admin_gallery_index');

            $app['service.flashbag']->addSuccess($app['translator']->trans('success_msg', [
                "%operation%" => 'feltöltése',
                "%other%" => '',
            ], "admin_flash-bag"), 'admin_gallery_index');
            return $app->redirect($app['url_generator']->generate('admin_gallery_index', $parameters));
        }

        return $app['twig']->render('admin/gallery/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function removeAction(Request $request, Application $app, $id) {
        $em = $app['doctrine']->getManager();

        try {
            $Entity = $this->checkIfEntityExistsById($em, 'GalleryImage', $id);
            $em->remove($Entity);
            $em->flush();
            $app['service.flashbag']->addSuccess(
                $app['translator']->trans('success_msg', [
                    "%operation%" => 'törlés',
                    "%other%" => '',
                ], "admin_flash-bag"),
                'admin_gallery_index'
            );

        } catch (NotFoundException $ex) {
            $app['service.flashbag']->addError($ex->getMessage(), 'admin_gallery_index');
        }

        return $app->redirect($app['url_generator']->generate('admin_gallery_index'));
    }
}
