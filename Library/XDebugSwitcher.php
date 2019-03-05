<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

/**
 * Class for switch xDebug for cli
 */
class XDebugSwitcher
{
    /**
     * xDebug .ini file path
     */
    const XDEBUG_CONFIG_FILE = '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini';

    /**
     * Temporary .ini file path
     */
    const TMP_CONFIG_FILE = '/tmp/xdebug.ini';

    /**
     * Switch Off xDebug
     *
     * @return void
     */
    public static function switchOff()
    {
        $command = sprintf(
            "sudo mv %s %s",
            static::XDEBUG_CONFIG_FILE,
            static::TMP_CONFIG_FILE
        );
        shell_exec($command);
    }

    /**
     * Switch On xDebug
     *
     * @return void
     */
    public static function switchOn()
    {
        $command = sprintf(
            "sudo mv %s %s",
            static::TMP_CONFIG_FILE,
            static::XDEBUG_CONFIG_FILE
        );
        shell_exec($command);
    }
}
