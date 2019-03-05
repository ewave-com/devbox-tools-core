<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

/**
 * Class for communication with database
 */
class Db
{
    /**
     * @var \PDO[]
     */
    private static $connections;

    /**
     * Get connection to database
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $dbName
     * @return \PDO
     */
    public static function getConnection($host, $user, $password, $dbName)
    {
        $key = sprintf('%s/%s/%s/%s', $host, $user, $password, $dbName);

        if (empty(static::$connections[$key])) {
            static::$connections[$key] = new \PDO(
                sprintf('mysql:dbname=%s;host=%s', $dbName, $host),
                $user,
                $password
            );
        }

        return static::$connections[$key];
    }
}
