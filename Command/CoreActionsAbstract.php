<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command;

use CoreDevBoxScripts\Library\JsonConfig;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for Application installation
 */
abstract class CoreActionsAbstract extends CommandAbstract
{
    /**
     * @var string
     */
    protected $configFile = '';

    /**
     * @var string
     */
    protected $commandCode = 'core';

    /**
     * @var string
     */
    protected $toolsName = 'Core commands';

    /**
     * @var string
     */
    protected $commandDesc = 'Core commands list';

    /**
     * @var string
     */
    protected $commandHelp = 'This command allows you to execute any of predefined actions to setup website';

    /**
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    abstract protected function getApplicationCommands();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->commandCode)
            ->setDescription($this->commandDesc)
            ->setHelp($this->commandHelp);

        $this->addOption('autostart', 'automode');
        $this->questionOnRepeat = 'Return back to ' . $this->toolsName . ' tools?';

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->beforeExecute($input, $output, $io);

        $io->section('Welcome to ' . $this->toolsName . ' Tools');
        $io->section('You can see all commands description by typing "platform-tools list"');

        $autostart = $input->getOption('autostart');
        if ($autostart) {
            $this->onAutoStart($input, $output, $io);
        }

        $this->executeRepeatedly('selectMenuPoint', $input, $output, $io);

        $this->afterExecute($input, $output, $io);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return $this
     */
    protected function beforeExecute($input, $output, $io)
    {
        return $this;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return $this
     */
    protected function afterExecute($input, $output, $io)
    {
        return $this;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return bool
     */
    protected function onAutoStart($input, $output, $io)
    {
        try {
            $commands = (array)JsonConfig::getConfig('auto_start_commands');
            if (empty($commands)) {
                return false;
            }
        } catch (\Exception $e) {
            $io->note($e->getMessage());
            $io->note('Step skipped.');
            return false;
        }

        foreach ($commands as $commandName => $state) {
            if ($command = $this->getApplication()->has($commandName)) {
                $io->writeln('Auto Execution of : ' . $commandName);
                $this->executeWrappedCommand($commandName, $input, $output);
            }
        }

        return true;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getCoreCommands()
    {
        return $this->getApplication()->all('core');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return bool
     */
    public function selectMenuPoint(InputInterface $input, OutputInterface $output, $io)
    {
        $default = 'exit';

        $commandsCore = $this->getCoreCommands();
        $commandsPlatform = $this->getApplicationCommands();

        $commands = array_merge($commandsCore, $commandsPlatform);

        $commandsFormatted = [];
        $c = 1;
        foreach ($commands as $k => $one) {
            $commandsFormatted[$c++] = $one->getName();
        }

        sort($commandsFormatted);
        array_unshift($commandsFormatted, $default);

        $operation = $io->choice('Select the сommand to execute', $commandsFormatted, $default);

        if (strtolower($operation) == 'exit') {
            $io->writeln('<info>' . 'bye bye.' . '</info>');
            return true;
        }

        $selected = $this->getApplication()->find($operation);
        if ($selected->getName()) {
            $this->executeWrappedCommand($selected->getName(), $input, $output);
        } else {
            $io->writeln('<notice>' . 'Could not recognize the command' . '</notice>');
        }

        return false;
    }
}
