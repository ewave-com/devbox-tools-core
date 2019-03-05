<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

use CoreDevBoxScripts\Framework\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$container = Container::getContainer();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.yaml');

\CoreDevBoxScripts\Framework\CommandRegistration::registerCommandPool(
    __DIR__ . '/Command/Pool',
    'CoreDevBoxScripts\\Command\\Pool'
);
