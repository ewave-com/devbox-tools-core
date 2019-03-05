<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework;

use Symfony\Component\Console\Application;

class EntryPoint
{
    /**
     * @param string $namespace
     * @return void
     */
    public static function run($namespace = '')
    {
        $container = Container::getContainer();
        $container->compile();

        /** @var Application $application */
        $application = $container->get(Application::class);
        if ($namespace) {
            $application->setDefaultCommand($namespace);
        }

        /** @var CommandPool $commandPool */
        $commandPool = $container->get(CommandPool::class);
        $application->addCommands($commandPool->getCommands());
        $application->run();
    }
}
