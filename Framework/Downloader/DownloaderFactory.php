<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework\Downloader;

class DownloaderFactory
{
    /**
     * @var DownloaderInterface[]
     */
    private $downloaders = [];

    /**
     * DownloaderFactory constructor.
     * @param array $downloaders
     */
    public function __construct(
        array $downloaders = []
    ) {
        $this->downloaders = $downloaders;
    }

    /**
     * @param string $sourceType
     * @return DownloaderInterface
     * @throws \Exception
     */
    public function get($sourceType)
    {
        if (empty($this->downloaders[$sourceType])) {
            throw new \Exception('Unsupported source type.');
        }
        return $this->downloaders[$sourceType];
    }
}
