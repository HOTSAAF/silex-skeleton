<?php

namespace App\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use App\Exception\NotFoundException;

class AdminController
{
    protected function getPaginatedResultByEntity(Request $request, Application $app, $Entity, $routeName = "admin_home")
    {
        $em = $app['doctrine']->getManager();

        $repo = $em->getRepository('\\App\\Entity\\'.$Entity);

        $query = $repo->findAllByParameters(
            $request->query->get('search'),
            $this->getOrderByArray($request->query->get('order', $this->getDefaultOrder()))
        );

        $paginator = $repo->paginate(
            $query,
            $request->query->get('page', 1),
            $app['config']['app']['admin']['result_page_size']
        );

        $paginator->renderer = function($data) use ($app, $routeName) {
            return $app['twig']->render('admin/includes/pager.html.twig', [
                'data' => $data,
                'routeName' => $routeName
            ]);
        };

        return $paginator;
    }

    protected function checkIfEntityExistsById($em, $entityName, $id)
    {
        $repo = $em->getRepository('\\App\\Entity\\'.$entityName);

        $Entity = $repo->findOneById($id);

        if ($Entity === null) {
            throw new NotFoundException('Unable to find '.$entityName.' entity.');
        }

        return $Entity;
    }

    protected function getOrderByArray($order) {
        $order = explode('.', $order);
        $orderArray = [
            $order[0] => $order[1],
        ];

        return $orderArray;
    }

    /**
     * @param $request Symfony Request Component
     * @param $app Silex\Application
     * @param $path_name String Route alias string
     * @return $form->getForm() Symfony Form Data
     *
     * This private function is responsible, that to create a filter Form which keep the other GET parameters
     */
    protected function createFilterForm(Request $request, $formFactory, $urlGenerator, $path_name = 'admin_home')
    {
        $form =
            $formFactory
                ->createNamedBuilder('', 'form', null, [
                    'attr' => [
                        'class' => 'c-filter__form',
                        'novalidate' => 'novalidate',
                    ],
                    'csrf_protection' => false,
                ])
                ->setAction($urlGenerator->generate($path_name))
                ->setMethod('GET')
                ->add('search', 'search', [
                    'label' => false,
                    'attr' => [
                        'class' => 'c-filter__input',
                        'placeholder' => 'Szabadszavas kereső',
                    ],
                ])
                ->add('page', 'hidden', [
                    'attr' => [
                        'value' => 1
                    ]
                ])
        ;

        $query_parameters = $request->query->all();

        if (!empty($query_parameters)) {
            foreach ($query_parameters as $key => $value) {
                if (!$form->has($key)) {
                    $form->add($key, 'hidden', ['data' => $value]);
                }
            }
        }

        $form = $form
            ->getForm()
            ->handleRequest($request)
        ;

        return $form;
    }
}
