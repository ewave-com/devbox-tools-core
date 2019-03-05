<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

use Symfony\Component\Console\Output\OutputInterface;

interface DownloaderInterface
{
    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @param array $options
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    public function download($sourcePath, $destinationPath, array $options = [], OutputInterface $output = null);
}
