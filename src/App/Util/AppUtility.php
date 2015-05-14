<?php

namespace App\Util;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;

class AppUtility
{
    private static $env = 'prod';

    public static function getFormErrorsForApi($form)
    {
        return [$form->getName() => self::getFormErrorsAsArray($form)];
    }

    public static function getFormErrorsAsArray($form)
    {
        $errors = [];

        if ($form instanceof Form) {
            if (count($form->getErrors()) > 0) {
                $errors['errors'] = [];
                foreach ($form->getErrors() as $formError) {
                    $errors['errors'][] = $formError->getMessage();
                }
            }

            if (count($form) > 0) {
                $childrenFormData = [];
                foreach ($form as $childForm) {
                    if ($childForm->isSubmitted() && $childForm->isValid()) {
                        continue;
                    }

                    $childrenFormData[$childForm->getName()] = self::getFormErrorsAsArray($childForm);
                }

                if (!empty($childrenFormData)) {
                    $errors['children'] = $childrenFormData;
                }
            }
        }

        return $errors;
    }

    public static function loadAppConfiguration($container)
    {
        $parameters = self::loadAppConfigYml('parameters');
        $paramKeys = array_keys($parameters['parameters']);
        $arrayValues = array_values($parameters['parameters']);

        foreach ($paramKeys as $key => $value) {
           $paramKeys[$key] = "%{$value}%";
        }

        $configFileContent = self::loadAppConfigString('config');
        $configFileContent = str_replace($paramKeys, $arrayValues, $configFileContent);

        $container['config'] = Yaml::parse($configFileContent);
    }

    public static function loadEnvAppConfiguration($container)
    {
        // Loading the configuration parameters
        $configPath = __DIR__.'/../../../src/config/config_prod.yml';
        if (!is_file($configPath)) {
            throw new \Exception('Missing "config_prod.yml" file.');
        }

        $config = Yaml::parse(file_get_contents($configPath));
        self::registerArrayIntoContainer($config, $container);

        // Development env config
        if (self::$env == 'dev') {
            $configPath = __DIR__.'/../../../src/config/config_dev.yml';
            if (is_file($configPath)) {
                $config = Yaml::parse(file_get_contents($configPath));
                self::registerArrayIntoContainer($config, $container);
            }
        }
    }

    public static function loadAppConfigString($yml_name)
    {
        // Loading the configuration
        $configPath = __DIR__.'/../../../src/config/'.$yml_name.'.yml';
        if (!is_file($configPath)) {
            $exception_msg = 'Missing "'.$yml_name.'" file. ';
            if ($yml_name == 'parameters') {
                $exception_msg .= 'Use the "src/config/'.$yml_name.'.dist" file as a base to create it.';
            }

            throw new \Exception($exception_msg);
        }

        return file_get_contents($configPath);
    }

    public static function loadAppConfigYml($yml_name)
    {
        // Loading the configuration
        $configPath = __DIR__.'/../../../src/config/'.$yml_name.'.yml';
        if (!is_file($configPath)) {
            $exception_msg = 'Missing "'.$yml_name.'" file. ';
            if ($yml_name == 'parameters') {
                $exception_msg .= 'Use the "src/config/'.$yml_name.'.dist" file as a base to create it.';
            }

            throw new \Exception($exception_msg);
        }

        return Yaml::parse(file_get_contents($configPath));
    }

    private static function registerArrayIntoContainer($array, $container)
    {
        if ($array === null || !is_array($array)) {
            return;
        }

        foreach ($array as $key => $value) {
            $container[$key] = $value;
        }
    }

    public static function getUploadedFileByPath($path)
    {
        return new UploadedFile(
            $path,
            basename($path),
            image_type_to_mime_type(exif_imagetype($path)),
            filesize($path),
            null,
            true
        );
    }

    public static function setEnv($env)
    {
        self::$env = $env;
    }

    public static function getEnv()
    {
        return self::$env;
    }

    public static function getSlug($rawString)
    {
        return  preg_replace(
                "/[^A-Za-z0-9-]/",
                '',
                str_replace(
                    ' ',
                    '-',
                    strtolower(self::removeAccents($rawString))
                )
            )
        ;
    }

    private static function removeAccents($str)
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');

        return str_replace($a, $b, $str);
    }
}
