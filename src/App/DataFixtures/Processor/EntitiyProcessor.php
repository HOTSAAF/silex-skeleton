<?php

namespace App\DataFixtures\Processor;

use Pimple\Container;
use Nelmio\Alice\ProcessorInterface;
use App\Entity\Article;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityProcessor implements ProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function preProcess($object)
    {
    }

    public function postProcess($object)
    {
        if ($object instanceof Article) {
            $this->postProcessArticle($object);
        }
    }

    private function postProcessArticle($object)
    {
        $faker = \Faker\Factory::create();
        $em = $this->container->get('doctrine')->getManager();


    }
}
