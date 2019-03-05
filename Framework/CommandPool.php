<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Framework;

class CommandPool
{
    /**
     * @var array
     */
    private $commands = [];

    /**
     * Container constructor.
     * @param array $commands
     */
    public function __construct(
        ...$commands
    ) {
        $this->commands = $commands;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
