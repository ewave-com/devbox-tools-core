<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

use CoreDevBoxScripts\Library\Cli;
use CoreDevBoxScripts\Library\Directory;
use Symfony\Component\Console\Output\OutputInterface;

class Vcs implements DownloaderInterface
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
        if (empty($options['source_branch'])) {
            throw new \Exception('Default Branch is not specified.');
        }

        $defaultBranch = $options['source_branch'];
        if (Directory::isDir($destinationPath . '/.git')) {
            if (null !== $output) {
                $output->writeln([
                    "<comment>Detected .git directory at destination path: $destinationPath</comment>",
                    "<comment>Cloning step will be skipped</comment>",
                    "<info>Trying to pull... </info>",
                ]);
            }
            $this->pull($destinationPath, $defaultBranch, $output);
        } else {
            $this->init($destinationPath, $sourcePath, $defaultBranch, $output);
        }

        $this->setupConfigs($destinationPath, $output);
    }

    /**
     * @param $gitPath
     * @param $branch
     * @param OutputInterface|null $output
     */
    protected function pull($gitPath, $branch, OutputInterface $output = null)
    {
        $this->cli->executeCommands(
            [
                "cd $gitPath && sudo -u www-data git pull origin $branch"
            ],
            $output
        );
    }

    /**
     * @param $gitPath
     * @param $origin
     * @param $branch
     * @param OutputInterface|null $output
     */
    protected function init($gitPath, $origin, $branch, OutputInterface $output = null)
    {
        $this->cli->executeCommands(
            [
                "rm -rf $gitPath/.git/*",
                "cd $gitPath && git init",
                "cd $gitPath && git remote add origin $origin",
                "cd $gitPath && git fetch origin $branch",
                "cd $gitPath && git checkout $branch",
            ],
            $output
        );
    }

    /**
     * @param $gitPath
     * @param OutputInterface|null $output
     */
    protected function setupConfigs($gitPath, OutputInterface $output = null)
    {
        $this->cli->executeCommands(
            [
                "cd $gitPath && git config core.fileMode false",
                "cd $gitPath && git config core.autocrlf input",
                "cd $gitPath && git config core.eol lf",
                "cd $gitPath && git config credential.helper store",
            ],
            $output
        );
    }
}
