<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class VerifiedReCaptcha extends Constraint
{
    // Gets translated in the "validators.yml" file.
    public $message = 'recaptcha_error';

    // Needed if the validator is a service, which has dependencies.
    public function validatedBy()
    {
        return 'validator.recaptcha';
    }
}
