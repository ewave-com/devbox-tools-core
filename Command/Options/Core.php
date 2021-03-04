<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Options;

/**
 * Container for Magento options
 */
class Core extends AbstractOptions
{
    const SOURCES_REUSE = 'core-sources-reuse';
    const SOURCES_SECONDARY_REUSE = 'core-secondary-sources-reuse';
    const MEDIA_CLEAR_TEMP_LOCAL = 'core-clear-tempt-storage';
    const MEDIA_DOWNLOAD = 'core-media-reuse';

    /**
     * {@inheritdoc}
     */
    protected static function getOptions()
    {
        return [
            static::SOURCES_REUSE => [
                'boolean' => true,
                'description' => 'Whether to use existing sources.',
                'question' => 'Do you want to update source code? %default%',
                'default' => true
            ],
            static::SOURCES_SECONDARY_REUSE => [
                'boolean' => true,
                'description' => 'Whether to use existing sources.',
                'question' => 'Do you want to update source code from the secondary repository? %default%',
                'default' => true
            ],
            static::MEDIA_DOWNLOAD => [
                'boolean' => true,
                'description' => 'Whether to use existing Media files.',
                'question' => 'Do you want to update media files? %default%',
                'default' => true
            ],
            static::MEDIA_CLEAR_TEMP_LOCAL => [
                'boolean' => true,
                'description' => 'Whether to use existing files.',
                'question' => 'Local folder for templorary files storing is not empty. '
                    . 'You have to clear it to do clone from git repo. Do you want me to do it? %default%'
            ],
        ];
    }
}
