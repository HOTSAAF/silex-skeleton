<?php

namespace App\Service;

class MobileDetectService
{
    private $mobileDetectLib;
    private $methodsToCall;
    private $htmlClassPrefix;

    public function __construct($mobileDetectLib, array $methodsToCall, $htmlClassPrefix = 'md_')
    {
        $this->mobileDetectLib = $mobileDetectLib;
        $this->methodsToCall = $methodsToCall;
        $this->htmlClassPrefix = $htmlClassPrefix;
    }

    public function getHtmlClasses()
    {
        $classes = [];
        foreach ($this->methodsToCall as $mdMethod) {
            if ($this->mobileDetectLib->{$mdMethod}()) {
                $classes[] = $this->htmlClassPrefix . $mdMethod;
            }
        }

        return $classes;
    }
}
