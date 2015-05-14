<?php

namespace App\Service;

use App\Exception\ApiException;

class FlashBagService
{
    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    private function add($msg, $type = "info", $prefix)
    {
        $prefix = $prefix !== null ? $prefix . '_' : '';
        $this->session->getFlashBag()->add($prefix . $type, $msg);
    }

    public function addInfo($msg, $prefix = null)
    {
        $this->add($msg, 'info', $prefix);
    }

    public function addWarning($msg, $prefix = null)
    {
        $this->add($msg, 'warning', $prefix);
    }

    public function addError($msg, $prefix = null)
    {
        $this->add($msg, 'error', $prefix);
    }

    public function addSuccess($msg, $prefix = null)
    {
        $this->add($msg, 'success', $prefix);
    }
}
