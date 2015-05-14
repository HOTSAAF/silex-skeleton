<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use ReCaptcha\ReCaptcha;

class VerifiedReCaptchaValidator extends ConstraintValidator
{
    private $reCaptchaConfig = null;
    private $request = null;

    public function __construct($reCaptchaConfig, $request_stack)
    {
        $this->reCaptchaConfig = $reCaptchaConfig;
        $this->request = $request_stack->getMasterRequest();
    }

    public function validate($recaptchaResponse, Constraint $constraint)
    {
        $recaptcha = new ReCaptcha($this->reCaptchaConfig['secret_key']);
        $reCaptchaResp = $recaptcha->verify(
            $recaptchaResponse,
            $this->request->getClientIp()
        );

        if (!$reCaptchaResp->isSuccess()) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();

            // The error codes in case they are needed for some reason
            // $reCaptchaResp->getErrorCodes()
        }
    }
}
