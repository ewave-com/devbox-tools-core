<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Abstract command for all devbox commands
 */
abstract class CommandAbstract extends CoreCommandAbstract
{
    /**
     * @var array
     */
    private $sharedData = [];

    /**
     * @var
     */
    protected $questionOnRepeat = 'Do you want to Continue?';

    /**
     * @param string          $method
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           ...$params
     * @return void
     */
    protected function executeRepeatedly($method, InputInterface $input, OutputInterface $output, ...$params)
    {
        $io = new SymfonyStyle($input, $output);
        $cont = true;

        $fixedParams[] = $input;
        $fixedParams[] = $output;
        $args = array_merge($fixedParams, $params);

        while ($cont === true) {
            $result = false;
            try {
                $result = call_user_func_array([$this, $method], $args);
            } catch (\Exception $e) {
                $io->note([$e->getMessage()]);
            }

            if ($result === false) {
                if (!$io->confirm($this->questionOnRepeat, 'Y')) {
                    break;
                }
            } else {
                $cont = false;
            }
        }
    }

    /**
     * @param array|string         $commands
     * @param OutputInterface|null $output
     * @throws \Exception
     * @return void
     */
    protected function executeCommands($commands, OutputInterface $output = null)
    {
        \CoreDevBoxScripts\Library\Cli::executeCommands($commands, $output);
    }

    /**
     * @param SymfonyStyle $io
     * @param string       $title
     * @return bool
     */
    protected function commandTitle($io, $title)
    {
        $io->section($title);
        return false;
    }

    /**
     * Execute wrapped commands
     *
     * @param array|string    $commandNames
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     * @throws CommandNotFoundException
     */
    protected function executeWrappedCommands($commandNames, InputInterface $input, OutputInterface $output)
    {
        $commandNames = (array)$commandNames;
        foreach ($commandNames as $commandName) {
            $this->executeWrappedCommand($commandName, $input, $output);
        }
    }

    /**
     * Execute wrapped command
     *
     * @param string          $commandName
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     * @throws CommandNotFoundException
     */
    protected function executeWrappedCommand($commandName, InputInterface $input, OutputInterface $output)
    {
        /** @var CoreCommandAbstract $command */
        $command = $this->getApplication()->get($commandName);
        $arguments = [null, $commandName];

        //Set values for options supported by the current command
        foreach ($command->getOptionsConfig() as $optionName => $optionConfig) {
            //Only if option is not virtual (defined as a valid CLI option)
            //And only if value was passed through CLI or was set in one of previously executed commands
            if (!$this->getConfigValue('virtual', $optionConfig, false)
                && ($input->hasParameterOption('--' . $optionName) || array_key_exists($optionName, $this->sharedData))
            ) {
                //Value set in previously executed command overwrites value originally passed through CLI
                $optionValue = array_key_exists($optionName, $this->sharedData)
                    ? $this->sharedData[$optionName]
                    : $input->getOption($optionName);

                //Value transformation for boolean type
                if ($this->getConfigValue('boolean', $optionConfig, false)) {
                    $optionValue = $optionValue ? static::SYMBOL_BOOLEAN_TRUE : static::SYMBOL_BOOLEAN_FALSE;
                }

                $arguments[] = sprintf('--%s=%s', $optionName, $optionValue);
            }
        }

        //Manually create new input for the command so it passes validation
        $commandInput = new ArgvInput($arguments);
        $commandInput->setInteractive($input->isInteractive());
        $command->run($commandInput, $output);

        //Store values that were set during current command execution for future commands
        foreach ($command->getValueSetStates() as $optionName => $optionState) {
            if ($optionState) {
                $this->sharedData[$optionName] = $commandInput->getOption($optionName);
            }
        }
    }

    /**
     * @param string          $filePath
     * @param OutputInterface $output
     * @return string
     */
    public function unGz($filePath, $output)
    {
        $path_parts = pathinfo($filePath);
        $newPath = $filePath;

        if ($path_parts['extension'] == 'gz') {
            $command = "gunzip " . $filePath;
            $output->writeln('<comment>Unpacking file...</comment>');
            $this->executeCommands(
                $command,
                $output
            );
            $newPath = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'];
            $output->writeln("<info>Extracted file: $newPath </info>");
        }

        return $newPath;
    }

    /**
     * @param string               $directory
     * @param OutputInterface|null $output
     * @throws \Exception
     */
    public function mkdir($directory, OutputInterface $output = null)
    {
        $this->executeCommands(
            'mkdir -p ' . $directory,
            $output
        );
    }
}
