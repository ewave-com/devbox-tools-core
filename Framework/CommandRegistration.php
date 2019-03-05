<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework;

use Symfony\Component\DependencyInjection\Reference;

class CommandRegistration
{
    /**
     * @param string $poolDir
     * @param string $namespace
     */
    public static function registerCommandPool($poolDir, $namespace)
    {
        foreach (glob($poolDir . '/*.php') as $fileName) {
            $className = $namespace . '\\' . basename($fileName, '.php');
            Container::getContainer()->register($className, $className);
            Container::getContainer()->getDefinition(CommandPool::class)
                ->addArgument(new Reference($className));
        }
    }
}
