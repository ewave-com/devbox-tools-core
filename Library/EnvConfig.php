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
class EnvConfig
{
    /**
     * @var null
     */
    protected static $_conf = null;

    /**
     * @param $name
     * @param string $default
     * @return bool|string|array
     */
    public static function getValue($name, $default = '')
    {
        self::$_conf = EnvParser::envToArray('/var/www/docker-config/.env');

        if (is_array($name)) {
            $params = $name;
        } else {
            $params[$name] = '';
        }

        foreach ($params as $k => $one) {
            if (isset(self::$_conf[$k])) {
                $params[$k] = trim(self::$_conf[$k]);
            } elseif ($default) {
                $params[$k] = $default;
            } else {
                $params[$k] = false;
            }
        }

        if (is_array($name)) {
            return $params;
        } else {
            return $params[$name];
        }
    }
}
