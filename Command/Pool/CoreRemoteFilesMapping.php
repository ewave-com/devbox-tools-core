<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CommandAbstract;
use CoreDevBoxScripts\Framework\Container;
use CoreDevBoxScripts\Framework\Downloader\DownloaderFactory;
use CoreDevBoxScripts\Library\EnvConfig;
use CoreDevBoxScripts\Library\JsonConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for downloading Magento sources
 */
class CoreRemoteFilesMapping extends CommandAbstract
{

    protected $configFile = '';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->configFile = EnvConfig::getValue('PROJECT_CONFIGURATION_FILE');
        $this->setName('core:remote-files:download')
            ->setDescription(
                'Download Files from Remote Sources [' . $this->configFile . ' file will be used as configuration]'
            )
            ->setHelp(
                'Download Files from Remote Sources [' . $this->configFile . ' file will be used as configuration]'
            );

        $this->questionOnRepeat = 'Try to download remote files again?';

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->executeRepeatedly('downloadRemoteFiles', $input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    protected function downloadRemoteFiles(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->commandTitle($io, 'Configuration files sync.');

        $output->writeln('<info>Config files download Params:</info>');

        $sourceType = JsonConfig::getConfig('sources->files_mapping->creds->source_type');
        $sourcePath = JsonConfig::getConfig('sources->files_mapping->creds->source_path');
        $destinationPath = JsonConfig::getConfig('sources->files_mapping->creds->local_temp_path');
        $downloadOptions = JsonConfig::getConfig('sources->files_mapping->creds');

        if (!file_exists($this->configFile)) {
            $io->note(
                'The file: ' . $this->configFile
                    . ' is not exist. Please check the PROJECT_CONFIGURATION_FILE parameter in .env'
            );
            $io->note('Step skipped.');
            return false;
        }

        $mapping = JsonConfig::getConfig('sources->files_mapping->mapping');
        if (!is_array($mapping)) {
            $io->note('files_mapping parameter doesn\'t exist in .env-project file');
            $io->note('Step skipped.');
            return false;
        }

        $sourceLogin = JsonConfig::getConfig('sources->files_mapping->creds->source_login');
        $sourcePassword = JsonConfig::getConfig('sources->files_mapping->creds->source_password');
        if (!$sourceLogin || !$sourcePassword) {
            $output->writeln('<comment>Login/password are not set in your .env-project.json. You can specify it at path "sources->files_mapping->creds->source_login" and "sources->files_mapping->creds->source_password" if you need</comment>');
            $output->writeln('<comment>Please input credentials for current operation.</comment>');
            if (!$sourceLogin) {
                $sourceLogin = $io->ask("Your login for $sourcePath", '');
                $downloadOptions['source_login'] = $sourceLogin;
            }
            if(!$sourcePassword) {
                $sourcePassword = $io->ask("Your password for $sourcePath", '');
                $downloadOptions['source_password'] = $sourcePassword;
            }
        }

        $headers = ['Remote File', 'Local file'];
        $rows = [];
        foreach ($mapping as $s => $l) {
            $one = [$s, $l];
            $rows[] = $one;
        }
        $io->table($headers, $rows);

        $this->mkdir($destinationPath, $output);

        /** @var DownloaderFactory $downloaderFactory */
        $downloaderFactory = Container::getContainer()->get(DownloaderFactory::class);
        $applicationRoot = EnvConfig::getValue('WEBSITE_SOURCES_ROOT') ?: EnvConfig::getValue('WEBSITE_DOCUMENT_ROOT');

        try {
            $downloader = $downloaderFactory->get($sourceType);
            foreach ($mapping as $sourceFile => $localFile) {
                $sourceFileFullPath = $sourcePath . '/' . $sourceFile;
                $fileFinalSPath = $destinationPath . DIRECTORY_SEPARATOR . $sourceFile;
                if (!is_dir(dirname($fileFinalSPath))) {
                    $this->mkdir(dirname($fileFinalSPath), $output);
                }
                $downloader->download($sourceFileFullPath, $fileFinalSPath, $downloadOptions, $output);
                $io->success('Downloaded: ' . $sourceFile);
            }
        } catch (\Exception $e) {
            $io->warning([$e->getMessage()]);
            $io->warning('Some issues appeared during file downloading.');
            return false;
        }

        foreach ($mapping as $sourceFile => $localFile) {
            $sourceTempFile = $destinationPath . DIRECTORY_SEPARATOR . $sourceFile;
            if ('/' !== substr($localFile, 0, 1)) {
                $localFile = $applicationRoot . '/' . $localFile; // transform to absolute path
            }

            if (!is_dir(dirname($localFile))) {
                $this->mkdir(dirname($localFile), $output);
            }

            $output->writeln('Copying file ' . $sourceTempFile . ' to ' . $localFile);
            $command = "cp $sourceTempFile $localFile";

            try {
                $this->executeCommands(
                    [$command],
                    $output
                );
            } catch (\Exception $e) {
                $io->note($e->getMessage());
                $io->note('Step skipped.');
                return false;
            }
        }

        return true;
    }
}
