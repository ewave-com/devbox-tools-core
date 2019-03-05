<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Options;

/**
 * Abstract class for option containers
 */
abstract class AbstractOptions
{
    /**
     * Get option config
     *
     * @param string $name
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public static function get($name, $options = [])
    {
        if (!array_key_exists($name, static::getOptions())) {
            throw new \Exception(sprintf('Option "%s" does not exist!', $name));
        }

        return array_replace_recursive(static::getOptions()[$name], $options);
    }

    /**
     * Get all options of one type
     *
     * @return array
     */
    abstract protected static function getOptions();

    /**
     * Check environment variable for value and return it if exists, otherwise return $value
     *
     * @param string $envName
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getDefaultValue($envName, $default)
    {
        $ret = $default;
        if (strlen(getenv($envName)) > 0) {
            $ret = (is_bool($default)) ? (boolean)getenv($envName) : getenv($envName);
        }
        return $ret;
    }
}
