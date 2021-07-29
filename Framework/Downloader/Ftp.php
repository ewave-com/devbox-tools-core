<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

use Symfony\Component\Console\Output\OutputInterface;

class Ftp implements DownloaderInterface
{
    const DEFAULT_PORT = 21;

    /**
     * {@inheritdoc}
     */
    public function download($sourcePath, $destinationPath, array $options = [], OutputInterface $output = null)
    {
        if (empty($options['source_host'])) {
            throw new \Exception('Source Host is not specified.');
        }

        $host = $options['source_host'];
        $port = isset($options['source_port']) ? $options['source_port']: self::DEFAULT_PORT;
        $login = isset($options['source_login']) ? $options['source_login']: 'anonymous';
        $password = isset($options['source_password']) ? $options['source_password'] : '';
        $sourcePath = ltrim($sourcePath, '/');
        $loginPath = "$login:$password@";

        $data = file_get_contents("ftp://$loginPath$host:$port/$sourcePath");
        file_put_contents($destinationPath, $data);
    }
}
