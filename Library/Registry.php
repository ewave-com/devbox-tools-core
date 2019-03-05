<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

/**
 * Class for communication between classes
 */
class Registry
{
    /**
     * @var array
     */
    private static $data = [];

    /**
     * Get value
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get($key)
    {
        return static::has($key) ? static::$data[$key] : null;
    }

    /**
     * Set value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        static::$data[$key] = $value;
    }

    /**
     * Set array of values
     *
     * @param array $data
     * @return void
     */
    public static function setData(array $data)
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }

    /**
     * Check if value exists
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return array_key_exists($key, static::$data);
    }

    /**
     * Check if all values exist
     *
     * @param array $keys
     * @return bool
     */
    public static function hasAll(array $keys)
    {
        foreach ($keys as $key) {
            if (!static::has($key)) {
                return false;
            }
        }

        return true;
    }
}
