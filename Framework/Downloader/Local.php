<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

use CoreDevBoxScripts\Library\Cli;
use Symfony\Component\Console\Output\OutputInterface;

class Local implements DownloaderInterface
{
    /**
     * @var Cli
     */
    protected $cli;

    /**
     * Vcs constructor.
     * @param Cli $cli
     */
    public function __construct(
        Cli $cli
    ) {
        $this->cli = $cli;
    }

    /**
     * {@inheritdoc}
     */
    public function download($sourcePath, $destinationPath, array $options = [], OutputInterface $output = null)
    {
        if (is_dir($sourcePath)) {
            $command = "cp -rf $sourcePath/* $destinationPath";
        } else if (file_exists($sourcePath)) {
            $command = "cp $sourcePath $destinationPath";
        }

        if (empty($command)) {
            throw new \Exception('Source Path is not exist:' . $sourcePath);
        }

        $this->cli->executeCommands(
            $command,
            $output
        );
    }
}
