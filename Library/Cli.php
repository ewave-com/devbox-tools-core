<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

use Symfony\Component\Console\Output\OutputInterface;

class Cli
{
    /**
     * @param array|string $commands
     * @param OutputInterface|null $output
     * @throws \Exception
     * @return void
     */
    public static function executeCommands($commands, OutputInterface $output = null)
    {
        $commands = (array)$commands;
        foreach ($commands as $command) {
            if ($output) {
                $output->writeln("<options=bold>Executing command: [ $command ] </>");
            }

            $start_date = new \DateTime();
            passthru($command, $returnCode);
            $end_date = new \DateTime();
            $since_start = $start_date->diff($end_date);

            if ($output) {
                $output->writeln([
                    "<options=bold>Duration: $since_start->h h. $since_start->i m. $since_start->s s. </>",
                    '================================',
                ]);
            }

            if ($returnCode > 0) {
                throw new \Exception('Command failed to execute. Return code: ' . $returnCode);
            }
        }
    }
}
