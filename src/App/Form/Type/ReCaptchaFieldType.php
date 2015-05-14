<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * "some_variable" would be available by accessing "form.vars.some_variable".
 * Every custom variable must be registered here, and passed to the template
 * with the help of the "buildView" method.
 */
class ReCaptchaFieldType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            // 'some_variable' => '',
            'error_bubbling' => false, // By default this is true... ><
        ]);
    }

    // public function buildView(FormView $view, FormInterface $form, array $options)
    // {
    //     parent::buildView($view, $form, $options);

    //     $view->vars = array_merge($view->vars, [
    //         'some_variable' => $options['some_variable']
    //     ]);
    // }

    // public function getParent()
    // {
    //     return 'choice';
    // }

    public function getName()
    {
        return 'recaptcha_field';
    }
}
