<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use App\Validator\Constraints as AppAssert;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label' => 'label_title',
                'attr' => [
                    "placeholder" => 'label_title',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'maxMessage' => 'article_form_name_length_max',
                        'minMessage' => 'article_form_name_length_min',
                        'min' => 5,
                        'max' => 250,
                    ]),
                ],
            ])
            ->add('description', 'textarea', [
                'label' => 'label_content',
                'attr' => [
                    'rows' => 20,
                    'class' => 'jsc-ck-editor'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('extract', 'textarea', [
                'label' => 'label_extract',
                'attr' => [
                    'rows' => 5,
                    "placeholder" => 'label_extract',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 350,
                    ]),
                ],
            ])
        ;

        if (!empty($options['edit_mode'])) {
        }

        $builder
            ->add('save', 'submit', [
                'label' => 'label_send_btn',
            ])
            ->add('cancel', 'reset', [
                'label' => 'label_reset_btn',
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // These options can be overwritten
        $resolver
            ->setDefaults([
                // This is only relevant for the labels, error messages are
                // translated from the "validators" domain.
                'translation_domain' => 'admin_article_form',
                'data_class' => 'App\Entity\Article', // If the form represents an Entity.
                'edit_mode' => false, // Option to handle different representations of the same form.
                // 'cascade_validation' => true,
                // 'csrf_protection' => true, // For Ajax forms it's useful to disable this.
                // 'validation_groups' => ['Default', 'Strict'], // Telling the validator explicitly to also check for the "Strict" group.
                // 'validation_groups' => false, // Disables validation altogether.
                // 'recaptcha_api_site_key' => '',
            ])
            // ->setRequired(['em'])
            // ->setRequired(['edit_mode'])
            // ->setAllowedTypes('em', 'Doctrine\Common\Persistence\ObjectManager')
            // ->setRequired(['disabled_names'])
            // ->setAllowedTypes('disabled_names', 'array')
        ;
    }

    public function getName()
    {
        return 'article_form';
    }
}
