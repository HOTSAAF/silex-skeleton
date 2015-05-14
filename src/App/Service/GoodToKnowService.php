<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;
use App\Exception\ApiException;

class GoodToKnowService
{
    private $goodToKnows;
    private $translator;

    public function __construct($configFilePath = '', $translator)
    {
        $this->translator = $translator;

        if (!is_file($configFilePath)) {
            throw new \Exception('Missing "' . $configFilePath . '" file.');
        }

        $this->goodToKnows = Yaml::parse(file_get_contents($configFilePath));
    }


    /*
    *   @params $group
    *   @return GoodToKnowCollection
    */
    public function getGoodToKnowCollection($group = null)
    {
        if ($group === null) {
            throw new \Exception('The "getGoodToKnowCollection" method needs a "group" parameter to work.');
        }

        $translatedStrings = [];
        foreach ($this->goodToKnows as $key) {
            if (!in_array($group, $key['groups'])) {
                continue;
            }

            $rawTransString = $this->translator->trans($key['key'], [], 'good_to_know');
            $params = $this->getTranslationParametersByString($rawTransString);
            $translatedStrings[] = $this->translator->trans($key['key'], $params, 'good_to_know');
        }

        return $translatedStrings;

    }

    /**
     * This method injects the parameters for any good-to-know translation
     * string. If a new parameter must be supported, then it have to be added
     * here.
     */
    private function getTranslationParametersByString($transString)
    {
        $params = [];

        if (strpos($transString, '%upload_max_filesize%') !== false) {
            $params['%upload_max_filesize%'] = ini_get('upload_max_filesize');
        }

        if (strpos($transString, '%consectetur%') !== false) {
            $params['%consectetur%'] = 'consectetur';
        }

        return $params;
    }
}
