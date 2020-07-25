<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Options;

/**
 * Container for database options
 */
/**
 * Class Db
 * @package CoreDevBoxScripts\Command\Options
 */
class Db extends AbstractOptions
{
    /**
     * @var string
     */
    const START = 'no';

    /**
     *@var string
     */
    const HOST = 'db-host';

    /**
     * @var string
     */
    const PORT = 'db-port';

    /**
     * @var string
     */
    const USER = 'db-user';

    /**
     * @var string
     */
    const PASSWORD = 'db-password';

    /**
     * @var string
     */
    const NAME = 'db-name';

    /**
     * string
     */
    const UPDATE_DB_DATA = 'update-db-data';

    /**
     * {@inheritdoc}
     */
    protected static function getOptions()
    {
        return [
            static::START => [
                'boolean' => true,
                'description' => 'Do you want to update Database?',
                'question' => 'Do you want to update DB from source dump? %default%',
                'default' => 'yes'
            ],
            static::HOST => [
                'default' => 'mysql',
                'description' => 'Mysql host',
                'question' => 'Please enter Mysql host %default%'
            ],
            static::PORT => [
                'default' => '3306',
                'description' => 'Mysql port',
                'question' => 'Please enter Mysql port %default%'
            ],
            static::USER => [
                'default' => 'root',
                'description' => 'Mysql user',
                'question' => 'Please enter Mysql user %default%'
            ],
            static::PASSWORD => [
                'default' => 'root',
                'description' => 'Mysql password',
                'question' => 'Please enter Mysql password %default%'
            ],
            static::NAME => [
                'default' => 'core2',
                'description' => 'Mysql database',
                'question' => 'Please enter Mysql database %default%'
            ],
            static::UPDATE_DB_DATA => [
                'boolean' => true,
                'description' => 'Do you want to update Database records?',
                'question' => 'Do you want to update DB values according to config? %default%',
                'default' => 'yes'
            ]
        ];
    }
}
