<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CommandAbstract;
use CoreDevBoxScripts\Library\EnvConfig;
use CoreDevBoxScripts\Library\JsonConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for some commands when devbox is ready
 */
class CoreSetupNodeModules extends CommandAbstract
{
    const DEFAULT_PACKAGE_MANAGER = 'npm';
    const DEFAULT_USE_SYMLINK = true;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('core:setup:node-modules')
            ->setDescription('Create symlink for node modules + install packages if required')
            ->setHelp('Create symlink for node modules + install packages if required');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $destinationPath = EnvConfig::getValue('WEBSITE_APPLICATION_ROOT') ?: EnvConfig::getValue('WEBSITE_DOCUMENT_ROOT');
        $io = new SymfonyStyle($input, $output);

        $packageManager = JsonConfig::getConfig(
            'configuration->node_modules->package_manager',
            self::DEFAULT_PACKAGE_MANAGER
        );

        $this->commandTitle($io, sprintf('Install %s packages', $packageManager));

        $useSymlink = JsonConfig::getConfig(
            'configuration->node_modules->use_symlink',
            self::DEFAULT_USE_SYMLINK
        );

        if ($useSymlink) {
            $command = "mkdir -p /var/www/node_modules_remote && ln -nfs /var/www/node_modules_remote $destinationPath/node_modules";
            $this->executeCommands(
                $command,
                $output
            );

            $io->comment('symlink created');
        }

        $operation = $io->confirm(sprintf('Install / Reinstall %s ?', strtoupper($packageManager)), false);

        if ($operation) {
            $checkPackageFileCommand = "test -e $destinationPath/package.json";
            $installCommand = sprintf("cd $destinationPath && %s install --force", $packageManager);
            $createPackageFileCommand = "cp $destinationPath/package.json.sample $destinationPath/package.json";
            $command = "$checkPackageFileCommand && $installCommand || $createPackageFileCommand && $installCommand";
            $this->executeCommands(
                $command,
                $output
            );
        }

        return true;
    }
}
