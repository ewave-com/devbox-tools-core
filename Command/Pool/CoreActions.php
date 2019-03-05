<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CoreActionsAbstract;

/**
 * Command for Magento installation
 */
class CoreActions extends CoreActionsAbstract
{
    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getApplicationCommands()
    {
        return $this->getApplication()->all('core');
    }
}
