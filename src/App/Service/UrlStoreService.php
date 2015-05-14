<?php

namespace App\Service;

class UrlStoreService
{

    private $session;
    private $request;
    private $router;
    private $keySuffix;

    public function __construct($session, $requestStack, $router, $keySuffix = '_saved_store_parameters')
    {
            $this->session = $session;
            $this->request = $requestStack->getCurrentRequest();
            $this->router = $router;
            $this->keySuffix = $keySuffix;
    }

    public function saveUrlByKey($key)
    {
        $this->session->set($key.$this->keySuffix, $this->request->query->all());
    }

    public function getUrlByKey($key)
    {
        $savedQueryParameters = $this->session->get($key.$this->keySuffix, []);

        return $this->router->generate($key, $savedQueryParameters);
    }

    public function getQueryByKey($key) {
        return $this->session->get($key.$this->keySuffix, []);
    }
}
