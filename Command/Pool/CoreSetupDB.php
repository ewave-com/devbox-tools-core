<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CommandAbstract;
use CoreDevBoxScripts\Command\Options\Db as DbOptions;
use CoreDevBoxScripts\Framework\Container;
use CoreDevBoxScripts\Framework\Downloader\DownloaderFactory;
use CoreDevBoxScripts\Library\JsonConfig;
use CoreDevBoxScripts\Library\EnvConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for Core final steps
 */
class CoreSetupDb extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('core:setup:db')
            ->setDescription('Fetch / Update Database')
            ->setHelp('Update DB');

        $this->questionOnRepeat = 'Try to update Database again?';

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
     * @throws \Exception
     * @return bool
     */
    protected function updateDatabase(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->commandTitle($io, 'Database sync.');

        $updateAgr = $this->requestOption(DbOptions::START, $input, $output, true);
        if (!$updateAgr) {
            $output->writeln('<comment>DB updating skipped</comment>');
            return true;
        }

        $sourceType = JsonConfig::getConfig('sources->db->source_type');
        $source = JsonConfig::getConfig('sources->db->source_path');
        $localDumpsStorage = JsonConfig::getConfig('sources->db->local_temp_path');
        $importGzippedSqlDirectly = JsonConfig::getConfig('sources->db->import_gzipped_sql_directly', true);
        $downloadOptions = JsonConfig::getConfig('sources->db');

        $sourceLogin = JsonConfig::getConfig('sources->db->source_login');
        $sourcePassword = JsonConfig::getConfig('sources->db->source_password');
        if (!$sourceLogin || !$sourcePassword) {
            $output->writeln('<comment>Login/password are not set in your .env-project.json. You can specify it at path "sources->db->source_login" and "sources->db->source_password" if you need</comment>');
            $output->writeln('<comment>Please input credentials for current operation.</comment>');
            if (!$sourceLogin) {
                $sourceLogin = $io->ask("Your login for $source", '');
                $downloadOptions['source_login'] = $sourceLogin;
            }
            if(!$sourcePassword) {
                $sourcePassword = $io->ask("Your password for $source", '');
                $downloadOptions['source_password'] = $sourcePassword;
            }
        }

        $coreHost = EnvConfig::getValue('WEBSITE_HOST_NAME');
        $projectName = EnvConfig::getValue('PROJECT_NAME');

        $mysqlHost = EnvConfig::getValue('CONTAINER_MYSQL_NAME');
        $mysqlHost = $projectName . '_' . $mysqlHost;
        $dbName = EnvConfig::getValue('CONTAINER_MYSQL_DB_NAME');
        $dbUser = 'root';
        $dbPassword = EnvConfig::getValue('CONTAINER_MYSQL_ROOT_PASS');

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
            ['Source', $source],
            ['Project URL', $coreHost],
            ['DB Host', $mysqlHost],
            ['DB Name', $dbName],
            ['DB User', $dbUser],
            ['DB Password', $dbPassword],
            ['DB dumps temp folder', $localDumpsStorage]
        ];
        $io->table($headers, $rows);

        if (!trim($source)) {
            throw new \Exception('Source path is not set in .env file. Recheck DATABASE_SOURCE_PATH parameter');
        }

        $isLocalFile = false;
        if (filter_var($source, FILTER_VALIDATE_URL) === false) {
            $isLocalFile = true;
        }

        $command = "mkdir -p " . $localDumpsStorage;
        $this->executeCommands(
            $command,
            $output
        );

        if (!$isLocalFile) {
            $fileFullPath = $localDumpsStorage . DIRECTORY_SEPARATOR . basename($source);

            /** @var DownloaderFactory $downloaderFactory */
            $downloaderFactory = Container::getContainer()->get(DownloaderFactory::class);

            try {
                $downloader = $downloaderFactory->get($sourceType);
                $downloader->download($source, $fileFullPath, $downloadOptions, $output);
                $io->success('Download completed');
            } catch (\Exception $e) {
                $io->warning([$e->getMessage()]);
                $io->warning('Some issues appeared during DB downloading.');
                return false;
            }
        } else {
            $fileFullPath = $source;
        }
        
        try {
            if (!$importGzippedSqlDirectly) {
                $output->writeln('<info>Extracting gzipped database ...</info>');
                    $newDumpPath = $this->unGz($fileFullPath, $output);
                    if (!is_file($newDumpPath)) {
                        throw new \Exception('File is not exists. Path: ' . $newDumpPath);
                    }
    
                    $output->writeln('<info>Importing Database dump...</info>');
                    $this->executeCommands(
                        "mysql -u$dbUser -p$dbPassword -h$mysqlHost $dbName < $newDumpPath",
                        $output
                    );
            } else {
                $output->writeln('<info>Importing Database dump...</info>');
                if ($this->commandExist('pv')) {
                    //import with progress bar if possible
                    if($this->isGzip($fileFullPath)) {
                        $this->executeCommands(
                            "pv $fileFullPath | gunzip | mysql -u$dbUser -p$dbPassword -h$mysqlHost $dbName",
                            $output
                        );
                    } else {
                        $this->executeCommands(
                            "pv $fileFullPath | mysql -u$dbUser -p$dbPassword -h$mysqlHost $dbName",
                            $output
                        );
                    }
                } else {
                    //import "on fly", without storing the dump file separately
                    if ($this->isGzip($fileFullPath)) {
                        $this->executeCommands(
                            "gunzip --stdout $fileFullPath | mysql -u$dbUser -p$dbPassword -h$mysqlHost $dbName",
                            $output
                        );
                    } else {
                        $this->executeCommands(
                            "mysql -u$dbUser -p$dbPassword -h$mysqlHost $dbName < $fileFullPath",
                            $output
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $io->note($e->getMessage());
            $io->warning('Some issues appeared during DB importing.');
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsConfig()
    {
        return [
            DbOptions::START => DbOptions::get(DbOptions::START),
            DbOptions::HOST => DbOptions::get(DbOptions::HOST),
            DbOptions::USER => DbOptions::get(DbOptions::USER),
            DbOptions::PASSWORD => DbOptions::get(DbOptions::PASSWORD),
            DbOptions::NAME => DbOptions::get(DbOptions::NAME),
        ];
    }
}
