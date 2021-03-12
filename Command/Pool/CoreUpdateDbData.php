<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CommandAbstract;
use CoreDevBoxScripts\Command\Options\Db as DbOptions;
use CoreDevBoxScripts\Library\Db;
use CoreDevBoxScripts\Library\EnvConfig;
use CoreDevBoxScripts\Library\JsonConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for Magento final steps
 */
class CoreUpdateDbData extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('core:setup:update-db-data')
             ->setDescription(
                 'Update DB table records according to config section "update_db_data"'
             )
             ->setHelp('Change DB table records');

        $this->questionOnRepeat = 'Try to update db again?';

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->executeRepeatedly('updateDatabase', $input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function updateDatabase(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->commandTitle($io, 'Update Data in DB');
        $tablesData = JsonConfig::getConfig('sources->update_db_data');
        if (!$tablesData) {
            $io->note('Updated database values are not configured in .env-projects.json in section "sources->update_db_data". Step skipped.');

            return true;
        }
        $updateAgr = $this->requestOption(DbOptions::UPDATE_DB_DATA, $input, $output, true);
        if (!$updateAgr) {
            $output->writeln('<comment>Urls updating skipped</comment>');

            return true;
        }

        $dbConnection = $this->getDbConnection($input, $output, $io);

        $tablesData = JsonConfig::getConfig('sources->update_db_data');
        if (!$tablesData) {
            $io->note('Updated database values are not set in .env-projects.json.');

            return true;
        }

        $hasError = false;
        foreach ($tablesData as $tableName => $values) {
            foreach ($values as $valueData) {
                try {
                    $query = $this->prepareQuery($dbConnection, $tableName, $valueData);
                    $output->writeln(sprintf('<info>%s</info>', $query));
//                    $output->writeln(sprintf('<info>Updating DB Record. Query: "%s"</info>', $query));
//                    $dbConnection->exec($query);
                } catch (\Exception $e) {
                    $io->note($e->getMessage());
                    $io->note('Record updating skipped.');
                    $hasError = true;
                }
            }
        }

        if (!$hasError) {
            $io->success('Db records has been updated');
        } else {
            $io->warning('Some issues appeared during DB updating');

            return false;
        }

        return true;
    }

    /**
     * @param \PDO $dbConnection
     * @param string $tableName
     * @param arrya $valueData
     *
     * @return string
     */
    protected function prepareQuery(\PDO $dbConnection, $tableName, array $valueData)
    {
        $valuesSql = null;
        $isDeleteQuery = false;
        switch (true) {
            case isset($valueData['set']):
                $valuesSql = $this->prepareSetQueryPart($dbConnection, $valueData);
                break;
            case isset($valueData['replace']):
                $valuesSql = $this->prepareReplaceQueryPart($dbConnection, $valueData);
                break;
            case isset($valueData['delete']):
                $isDeleteQuery = true;
                break;
        }

        $whereSql = $this->prepareWhereQueryPart($dbConnection, $valueData);
        $whereSql = $whereSql ? 'WHERE ' . $whereSql : '';

        if (!$isDeleteQuery) {
            $query = sprintf('UPDATE `%s` SET %s %s', $tableName, $valuesSql, $whereSql);
        } else {
            $query = sprintf('DELETE FROM `%s` %s', $tableName, $whereSql);
        }

        return $query;
    }

    /**
     * @param \PDO $dbConnection
     * @param array $valueData
     *
     * @return string
     */
    protected function prepareWhereQueryPart(\PDO $dbConnection, array $valueData)
    {
        $whereData = isset($valueData['where']) ? $valueData['where'] : [];

        $whereConditions = [];
        foreach ($whereData as $columnName => $value) {
            if (false === strpos($value, '%')) {
                $whereConditions[] = sprintf('`%s` = %s', $columnName, $dbConnection->quote($value));
            } else {
                $whereConditions[] = sprintf('`%s` LIKE %s', $columnName, $dbConnection->quote($value));
            }
        }

        return implode(' AND ', $whereConditions);
    }

    /**
     * @param \PDO $dbConnection
     * @param array $valueData
     *
     * @return string
     */
    protected function prepareSetQueryPart(\PDO $dbConnection, array $valueData)
    {
        $setData = isset($valueData['set']) ? $valueData['set'] : [];

        $columnName = current(array_keys($setData));
        $value = current(array_values($setData));

        $setValueSql = sprintf('`%s` = %s', $columnName, $value);

        return $setValueSql;
    }

    /**
     * @param \PDO $dbConnection
     * @param array $valueData
     */
    protected function prepareReplaceQueryPart(\PDO $dbConnection, array $valueData)
    {
        $replaceData = isset($valueData['replace']) ? $valueData['replace'] : [];

        $columnName = current(array_keys($replaceData));
        $value = current(array_values($replaceData));

        $from = isset($value['needle']) ? $value['needle'] : null;
        $to = isset($value['replacement']) ? $value['replacement'] : null;
        $replaceValueSql = null;
        if ($from && $to) {
            $replaceValueSql = sprintf(
                '`%s` = REPLACE(`%s`, %s, %s)',
                $columnName,
                $columnName,
                $dbConnection->quote($from),
                $dbConnection->quote($to)
            );
        }

        return $replaceValueSql;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     *
     * @return \PDO
     */
    protected function getDbConnection(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $magentoHost = EnvConfig::getValue('WEBSITE_HOST_NAME');
        $magentoProtocol = EnvConfig::getValue('WEBSITE_PROTOCOL');
        $projectName = EnvConfig::getValue('PROJECT_NAME');
        $mysqlHost = EnvConfig::getValue('CONTAINER_MYSQL_NAME');
        $dbPassword = EnvConfig::getValue('CONTAINER_MYSQL_ROOT_PASS');
        $dbName = EnvConfig::getValue('CONTAINER_MYSQL_DB_NAME');
        $dbUser = 'root';
        $mysqlHost = $projectName . '_' . $mysqlHost;

        if (!$mysqlHost || !$dbName || !$dbPassword) {
            $output->writeln('<comment>Some of required data are missed</comment>');
            $output->writeln('<comment>Reply on:</comment>');

            $mysqlHost = $input->getOption(DbOptions::HOST);
            $dbUser = $input->getOption(DbOptions::USER);
            $dbPassword = $input->getOption(DbOptions::PASSWORD);
            $dbName = $input->getOption(DbOptions::NAME);
        }

        $headers = ['Parameter', 'Value'];
        $rows = [
            ['Project URL', $magentoHost],
            ['Project Protocol', $magentoProtocol],
            ['DB Host', $mysqlHost],
            ['DB Name', $dbName],
            ['DB User', $dbUser],
            ['DB Password', $dbPassword],
        ];
        $io->table($headers, $rows);

        $dbConnection = Db::getConnection($mysqlHost, $dbUser, $dbPassword, $dbName);

        return $dbConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsConfig()
    {
        return [
            DbOptions::UPDATE_DB_DATA => DbOptions::get(DbOptions::UPDATE_DB_DATA),
            DbOptions::HOST           => DbOptions::get(DbOptions::HOST),
            DbOptions::USER           => DbOptions::get(DbOptions::USER),
            DbOptions::PASSWORD       => DbOptions::get(DbOptions::PASSWORD),
            DbOptions::NAME           => DbOptions::get(DbOptions::NAME),
        ];
    }
}
