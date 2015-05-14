<?php

namespace App\Service;

// use Symfony\Component\Form\Form;
// use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;
use App\Form\Type\ContactFormType;

/**
 * For Symfony creating a "FormService" is needless, since forms can be declared
 * as services there.
 */
class FormService
{
    private $formFactory;
    private $urlGenerator;
    private $reCaptchaConfig;
    private $request;

    public function __construct($formFactory, $urlGenerator, $reCaptchaConfig, $request_stack)
    {
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->reCaptchaConfig = $reCaptchaConfig;
        $this->request = $request_stack->getMasterRequest();
    }

    public function getContactForm()
    {
        // Fetching the "disabled_names" could come from a DB table, through
        // a service. For the sake of simplicity this example uses a yml file,
        // and the logic is executed right here instead of inside a service.
        $form = $this->formFactory->create(new ContactFormType(), null, [
            'action' => 'send_contact_mail',
            // 'csrf_protection' => false,
            'disabled_names' => Yaml::parse(file_get_contents(__DIR__ . '/../../config/contact_form_disabled_names.yml')),
            'recaptcha_api_site_key' => $this->reCaptchaConfig['site_key'],
        ]);

        // Since I couldn't change the recaptcha form field name from
        // "g-recaptcha-response" "to contact_form['recaptcha_response']", I
        // set the field manually here.
        if ($form->has('recaptcha_response')) {
            $form->get('recaptcha_response')->setData($this->request->request->get('g-recaptcha-response', null));
        }

        return $form;
    }
}
