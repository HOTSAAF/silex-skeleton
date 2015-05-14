<?php

namespace App\Exception;

class ApiException extends \Exception
{
    // Not all ApiException worth logging. (Like validation errors, etc.)
    // Some does though, ex: non-functioning MailChimp subscription.
    // Critical errors trigger mail and/or slack logger handlers.
    private $critical = false;

    // Redefine the exception so message isn't optional
    public function __construct($message, $critical = false, $code = null, Exception $previous = null)
    {
        $this->critical = $critical;
        parent::__construct($message, $code, $previous);
    }

    public function isCritical()
    {
        return $this->critical;
    }

    public function setIsCritical($critical)
    {
        return $this->critical = $critical;
    }
}
