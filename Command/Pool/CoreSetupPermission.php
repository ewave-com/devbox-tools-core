<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CommandAbstract;
use CoreDevBoxScripts\Library\JsonConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for downloading Core sources
 */
class CoreSetupPermission extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('core:setup:permissions')
            ->setDescription('Set Permissions to root folder')
            ->setHelp('Set Permissions to root folder [www-data]:775');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->commandTitle($io, 'Permissions setup');
        $output->writeln('<info>Setting files owner / permissions</info>');

        $sources = JsonConfig::getConfig('base_params->working_directories');
        foreach ($sources as $source) {
            $output->writeln('Permissions updating has been started for ' . $source);
            try {
                $command1 = "sudo chown -R www-data:www-data " . $source . '/*';
                $command2 = "chmod -R 777 " . $source . '/*';
                $command3 = sprintf('if [ -d %s/.ssh ]; then sudo chmod -R 600 %s/.ssh/*; fi', $source, $source);
                $command4 = "sudo chmod -R 777 /tmp/*";

                $this->executeCommands(
                    [$command1, $command2, $command3],
                    $output
                );

            } catch (\Exception $e) {
                $io->note($e->getMessage());
                $io->note('Step skipped. Not possible to continue with media updating');
                return false;
            }
        }

        if (!isset($e)) {
            $io->success('Permissions has been updated');
        } else {
            $io->warning('Some issues appeared during permissions updating');
            return false;
        }

        return true;
    }
}
