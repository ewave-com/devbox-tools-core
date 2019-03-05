<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

use Symfony\Component\Console\Output\OutputInterface;

class Http implements DownloaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function download($sourcePath, $destinationPath, array $options = [], OutputInterface $output = null)
    {
        $login = '';
        if (!empty($options['source_login']) && !empty($options['source_password'])) {
            $login = $options['source_login'] . ":" . $options['source_password'];
        }

        if (null !== $output) {
            $output->writeln([
                'Http Downloading...',
                $sourcePath,
                'Login: ' . $login
            ]);
        }

        set_time_limit(0);
        if (is_file($destinationPath)) {
            unlink($destinationPath);
        }

        $fp = fopen($destinationPath, 'w+');
        $ch = curl_init($sourcePath);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($login) {
            curl_setopt($ch, CURLOPT_USERPWD, $login);
        }

        if (curl_exec($ch) === false) {
            throw new \Exception(curl_error($ch));
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpcode) {
            throw new \Exception(sprintf('Download failed. Response code: %s', $httpcode));
        }

        curl_close($ch);
        fclose($fp);
    }
}
