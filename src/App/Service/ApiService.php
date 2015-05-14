<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Exception\ApiException;

class ApiService
{
    const STATE_ERROR = 1;
    const STATE_SUCCESS = 2;
    const STATE_WARNING = 3;

    private $request;

    public function __construct($request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();
    }

    public function getMandatoryGetParameters($mandatoryParameterKeys)
    {
        return $this->getMandatoryParameters($mandatoryParameterKeys);
    }

    public function getMandatoryPostParameters($mandatoryParameterKeys)
    {
        return $this->getMandatoryParameters($mandatoryParameterKeys, true);
    }

    public function getResponseData($data = true, $state = true)
    {
        if ($state === true) {
            $state = 'success';
        } else if ($state === false || !is_string($state)) {
            $state = 'error';
        }

        $this->checkIfStateConstantExists($state);

        $responseData = [
            'state' => $state,
            'response' => $data,
        ];

        return $responseData;
    }

    public function getResponse($data = true, $state = true)
    {
        return new JsonResponse($this->getResponseData($data, $state), 200);
    }

    private function getMandatoryParameters($mandatoryParameterKeys, $postIsExpected = false)
    {
        if (!is_array($mandatoryParameterKeys)) {
            throw new ApiException('The "mandatoryParameterKeys" parameter for the "getMandatoryParameters" service call must be an array.');
        }

        $requestParameterBag = null;
        if ($postIsExpected) {
            $requestParameterBag = $this->request->request;
        } else {
            $requestParameterBag = $this->request->query;
        }

        $parameters = array();
        foreach ($mandatoryParameterKeys as $parameterName) {
            $parameter = $requestParameterBag->get($parameterName, null);
            if ($parameter === null) {
                throw new ApiException('Missing "' . $parameterName . '" parameter.');
            }

            $parameters[$parameterName] = $parameter;
        }

        return $parameters;
    }

    private function checkIfStateConstantExists($constantName)
    {
        // Throws exception if not defined
        constant(
            'App\\Service\\ApiService::STATE_' .
            strtoupper($constantName)
        );
    }
}
