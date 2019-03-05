<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Container
{
    /**
     * @var ContainerBuilder
     */
    private static $container;

    /**
     * Container constructor.
     */
    private function __construct()
    {
        //singleton
    }

    /**
     * @return ContainerBuilder
     */
    public static function getContainer()
    {
        if (null === self::$container) {
            self::$container = new ContainerBuilder();
        }
        return self::$container;
    }
}
