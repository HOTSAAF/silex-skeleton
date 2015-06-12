<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use App\Validator\Constraints as AppAssert;

// use Symfony\Component\Form\FormEvent;
// use Symfony\Component\Form\FormEvents;
// use Symfony\Component\Form\FormError;
// use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

/**
 * For a list of available Constraints visit http://symfony.com/doc/master/book/validation.html#supported-constraints
 * Here you can check for available parameters, like translation messages, and
 * for examples of default values.
 */
class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // The name field with customised error messages (validators.yml)
            // Since every form's every error message is in the same yml file,
            // messages about this form are prefixed with it's name:
            // "contact_form"
            ->add('name', null, [
                'label' => 'label_name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'contact_form_name_not_blank',
                        'groups' => ['Strict'], // This means, that providing the name is only mandatory in the 'strict' group.
                    ]),
                    new Assert\Length([
                        'minMessage' => 'contact_form_name_length_min',
                        'maxMessage' => 'contact_form_name_length_max',
                        'min' => 2,
                        'max' => 50,
                    ]),
                    new Assert\Callback(function ($nameValue, ExecutionContextInterface $context) use ($options) {
                        // Disabled names could come from anywhere, even from a DB table.
                        if (in_array($nameValue, $options['disabled_names'])) {
                            $context
                                ->buildViolation('contact_form_callback', ['{{ value }}' => $nameValue])
                                ->addViolation();
                        }
                    }),
                ],
                'attr' => [
                    'class' => 'asd qwe',
                ],
                // Lets a field's errors bubble up to the form-level errors.
                // This basically means, that the error will appear before the
                // form, and not before the specific field.
                // 'error_bubbling' => true,
            ])
            ->add('email', null, [
                'label' => 'label_email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('content', null, [
                'label' => 'label_content',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => 200,
                    ]),
                ],
            ])
            // Adding custom field type
            ->add('recaptcha_response', new ReCaptchaFieldType(), [
                'attr' => [
                    'class' => 'js-contact-form__recaptcha',
                ],
                'constraints' => [
                    // Adding custom constraint
                    new AppAssert\VerifiedReCaptcha(),
                ],
            ])
        ;

        $builder->add('button', 'submit', ['label' => 'label_send_btn']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // These options can be overwritten
        $resolver
            ->setDefaults([
                // This is only relevant for the labels, error messages are
                // translated from the "validators" domain.
                'translation_domain' => 'contact_form',
                // 'data_class' => 'App\Entity\Code', // If the form represents an Entity.
                // 'edit_mode' => false, // Option to handle different representations of the same form.
                // 'cascade_validation' => true,
                // 'csrf_protection' => true, // For Ajax forms it's useful to disable this.
                'validation_groups' => ['Default', 'Strict'], // Telling the validator explicitly to also check for the "Strict" group.
                // 'validation_groups' => false, // Disables validation altogether.
                'recaptcha_api_site_key' => '',
            ])
            // ->setRequired(['em'])
            // ->setRequired(['edit_mode'])
            // ->setAllowedTypes('em', 'Doctrine\Common\Persistence\ObjectManager')
            ->setRequired(['disabled_names'])
            ->setAllowedTypes('disabled_names', 'array')
        ;
    }

    public function getName()
    {
        return 'contact_form';
    }
}
