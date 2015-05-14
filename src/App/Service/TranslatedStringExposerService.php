<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;
use App\Exception\ApiException;

class TranslatedStringExposerService
{
    private $exposedDomains;
    private $request;
    private $translator;

    public function __construct($translator, $request_stack, $configFilePath = '')
    {
        $this->translator = $translator;

        if (!is_file($configFilePath)) {
            throw new \Exception('Missing "' . $configFilePath . '" file.');
        }

        $this->exposedDomains = Yaml::parse(file_get_contents($configFilePath));
        $this->request = $request_stack->getMasterRequest();
    }

    public function getExposedCollection($group = null)
    {
        $route = $this->request->get('_route');

        $translatedStrings = array();
        foreach ($this->exposedDomains as $domain => $exposedTransKeys) {
            foreach ($exposedTransKeys as $transKey) {
                if (
                    is_string($transKey) ||
                    !isset($transKey['groups'])
                ) {
                    if (isset($transKey['key'])) {
                        $translatedStrings[$domain][$transKey['key']] = $this->translator->trans($transKey['key'], [], $domain);
                    } else {
                        $translatedStrings[$domain][$transKey] = $this->translator->trans($transKey, [], $domain);
                    }
                } else {
                    if (
                        $group !== null &&
                        in_array($group, $transKey['groups'])
                    ) {
                        $translatedStrings[$domain][$transKey['key']] = $this->translator->trans($transKey['key'], [], $domain);
                    }
                }
            }
        }

        return $translatedStrings;
    }

}
