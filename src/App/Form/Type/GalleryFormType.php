<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use App\Validator\Constraints as AppAssert;

class GalleryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Simple image
        $imageOptions = [
            "label" => 'label_image',
            'attr' => [
                'accept' => 'image/*'
            ],
        ];

        $builder
            ->add('image', 'file', $imageOptions)
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
                'translation_domain' => 'admin_gallery_form',
                'data_class' => 'App\Entity\GalleryImage', // If the form represents an Entity.
                // 'edit_mode' => false, // Option to handle different representations of the same form.
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
        return 'gallery_form';
    }
}
