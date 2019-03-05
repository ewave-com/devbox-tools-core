<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

use Symfony\Component\Console\Output\OutputInterface;
use phpseclib\Net\SFTP as SftpLib;

class Sftp implements DownloaderInterface
{
    const DEFAULT_PORT = 22;
    const DEFAULT_TIMEOUT = 10;

    /**
     * {@inheritdoc}
     */
    public function download($sourcePath, $destinationPath, array $options = [], OutputInterface $output = null)
    {
        if (empty($options['source_host'])) {
            throw new \Exception('Source Host is not specified.');
        }

        $host = $options['source_host'];
        $port = $options['source_port'] ?? self::DEFAULT_PORT;
        $timeout = $options['source_timeout'] ?? self::DEFAULT_TIMEOUT;
        $login = $options['source_login'] ?? '';
        $password = $options['source_password'] ?? '';

        $sftp = new SftpLib($host, $port, $timeout);
        if (!$sftp->login($login, $password)) {
            throw new \Exception("Could not connect to $host on port $port");
        }

        if (!$sftp->get($sourcePath, $destinationPath)) {
            throw new \Exception("Could not save file: $sourcePath");
        }
    }
}
