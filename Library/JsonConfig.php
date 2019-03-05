<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

/**
 * Class EnvConfig
 * @package CoreDevBoxScripts\Library
 */
class JsonConfig
{
    /**
     * @var null
     */
    protected static $_conf = null;

    /**
     * @param string $name
     * @param string $default
     * @param bool $pattern
     * @return mixed
     * @throws \Exception
     */
    public static function getConfig($name = '', $default = '', $pattern = true)
    {
        $jsonFile = EnvConfig::getValue('PROJECT_CONFIGURATION_FILE', false);
        $jsonContent = file_get_contents($jsonFile);
        $jsonConfig = json_decode($jsonContent, true);

        if ($jsonConfig === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Project configuration file is incorrect, check ' . $jsonFile . ' file');
        }

        if ($name) {
            $nameParts = [];
            if (strpos($name, '->')) {
                $nameParts = explode('->', $name);
            } else {
                $nameParts[] = $name;
            }

            $value = self::getInDeep($jsonConfig, $nameParts, 0, $default);
            if ($pattern) {
                $value = JsonConfigPatterns::scroll($value);
            }

            return $value;
        }

        return false;
    }

    /**
     * @param array $array
     * @param array $parts
     * @param int $index
     * @param string $default
     * @return bool|string|array
     */
    protected function getInDeep($array, $parts, $index, $default = '')
    {
        if (isset($parts[$index])) {
            $key = $parts[$index];
            if (isset($array[$key])) {
                $index++;
                if ($index >= count($parts)) {
                    $result = $array[$key];
                    return $result;
                } else {
                    $result = self::getInDeep($array[$key], $parts, $index);
                    return $result;
                }
            } else {
                if ($default) {
                    $result = $default;
                    return $result;
                } else {
                    $result = $array;
                    return $result;
                }
            }
        } else {
            return false;
        }
    }
}
