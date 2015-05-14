<?php
namespace App\Util;

use Symfony\Component\Yaml\Yaml;

/**
 * This class validates a YML file based on a *dist version.
 * A YMl file is valid, if it's keys are the same as the dist* version's keys.
 * It uses the *nix `diff` command, so be careful when running using this on
 * Windows.
 */
class YmlDistImplementationValidator
{
    private $distPath;
    private $distTmpPath;
    private $checkPath;
    private $checkTmpPath;

    public function __construct($distPath, $checkPath)
    {
        $this->distPath = realpath(rtrim($distPath, '/'));
        $this->distTmpPath = $this->distPath . '~';
        if (!is_file($this->distPath)) {
            throw new \RuntimeException('The given "distPath" (' . $distPath . ') does not seem to be an existing file.');
        }

        $this->checkPath = realpath(rtrim($checkPath, '/'));
        $this->checkTmpPath = $this->checkPath . '~';
        if (!is_file($this->checkPath)) {
            throw new \RuntimeException('The given "checkPath" (' . $checkPath . ') does not seem to be an existing file.');
        }
    }

    public function validate()
    {
        $distYmlFile = Yaml::parse(file_get_contents($this->distPath));
        $distYmlFile = $this->replaceArrayValues($distYmlFile);
        file_put_contents($this->distTmpPath, Yaml::dump($distYmlFile, 10));

        $ymlFile = Yaml::parse(file_get_contents($this->checkPath));
        $ymlFile = $this->replaceArrayValues($ymlFile);
        file_put_contents($this->checkTmpPath, Yaml::dump($ymlFile, 10));

        $isValid = $distYmlFile === $ymlFile; // Checks for array key/value pars and even orders.

        if (!$isValid) {
            throw new \RuntimeException("
                Differences were found in the following two yaml files:
                \"{$this->distPath}\" and
                \"{$this->checkPath}\".
                The files which are stripped from the values can be found at the following locations:
                \"{$this->distTmpPath}\" and
                \"{$this->checkTmpPath}\".
            ");
        }
    }

    /**
     * Replaces an array's values with the value provided as the second
     * parameter.
     */
    private function replaceArrayValues($array, $replaceValue = null)
    {
        if (is_array($array)) {
            foreach ($array as $key => $arrayValue) {
                $array[$key] = $this->replaceArrayValues($arrayValue);
            }

            return $array;
        }

        return $replaceValue;
    }
}
