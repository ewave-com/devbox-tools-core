<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Command\Pool;

use CoreDevBoxScripts\Command\CommandAbstract;
use CoreDevBoxScripts\Command\Options\Core as CoreOptions;
use CoreDevBoxScripts\Framework\Container;
use CoreDevBoxScripts\Framework\Downloader\DownloaderFactory;
use CoreDevBoxScripts\Library\Registry;
use CoreDevBoxScripts\Library\EnvConfig;
use CoreDevBoxScripts\Library\JsonConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for downloading Core sources
 */
class CoreSetupCode extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('core:setup:code')
            ->setDescription('Download Core Source Code')
            ->setHelp('This command allows you to download Core source code from repo.');

        $this->questionOnRepeat = 'Try to download the code again?';

        parent::configure();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->commandTitle($io, 'Source Code Download');
        $useExistingSources = $this->requestOption(CoreOptions::SOURCES_REUSE, $input, $output, true);

        if ($useExistingSources) {
            $this->executeRepeatedly('copyFromGit', $input, $output, $io);
        } else {
            $output->writeln('<comment>Source code downloading step skipped.</comment>');
        }

        Registry::set(CoreOptions::SOURCES_REUSE, $useExistingSources);

        if (JsonConfig::getConfig('sources->code_secondary')) {
            $useExistingSecondarySources = $this->requestOption(CoreOptions::SOURCES_SECONDARY_REUSE, $input, $output, true);
            if ($useExistingSecondarySources) {
                $this->executeRepeatedly('copyFromSecondaryGit', $input, $output, $io);
            } else {
                $output->writeln('<comment>Secondary repository source code downloading step skipped.</comment>');
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $io
     * @return bool
     */
    protected function copyFromGit(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $destinationPath = EnvConfig::getValue('WEBSITE_SOURCES_ROOT') ?: EnvConfig::getValue('WEBSITE_DOCUMENT_ROOT');
        $sourceType = JsonConfig::getConfig('sources->code->source_type');
        $sourcePath = JsonConfig::getConfig('sources->code->source_path');
        $defaultBranch = JsonConfig::getConfig('sources->code->source_branch');
        $downloadOptions = JsonConfig::getConfig('sources->code');

        $output->writeln('<info>[Download Params]</info>');
        $headers = ['Parameter', 'Value'];
        $rows = [
            ['Remote Url', $sourcePath],
            ['Branch', $defaultBranch],
            ['Destination', $destinationPath],
        ];
        $io->table($headers, $rows);

        $this->executeCommands(
            ["mkdir -p " . $destinationPath],
            $output
        );

        /** @var DownloaderFactory $downloaderFactory */
        $downloaderFactory = Container::getContainer()->get(DownloaderFactory::class);

        try {
            $downloader = $downloaderFactory->get($sourceType);
            $downloader->download($sourcePath, $destinationPath, $downloadOptions, $output);
            $io->success('Download completed');
        } catch (\Exception $e) {
            $io->warning([$e->getMessage()]);
            $io->warning('Some issues appeared during source code update');
            return false;
        }

        $rComposer = $io->confirm('Run "composer install?"', false);
        if ($rComposer) {
            $this->executeCommands("cd $destinationPath && composer install", $output);
        }

        return true;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $io
     * @return bool
     */
    protected function copyFromSecondaryGit(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $sourceType = JsonConfig::getConfig('sources->code_secondary->source_type');
        $sourcePath = JsonConfig::getConfig('sources->code_secondary->source_path');
        $destinationPath = JsonConfig::getConfig('sources->code_secondary->destination_website_path');
        $defaultBranch = JsonConfig::getConfig('sources->code_secondary->source_branch');
        $downloadOptions = JsonConfig::getConfig('sources->code_secondary');

        $output->writeln('<info>[Secondary Download Params]</info>');
        $headers = ['Parameter', 'Value'];
        $rows = [
            ['Remote Url', $sourcePath],
            ['Branch', $defaultBranch],
            ['Destination', $destinationPath],
        ];
        $io->table($headers, $rows);

        $this->executeCommands(
            ["mkdir -p " . $destinationPath],
            $output
        );

        /** @var DownloaderFactory $downloaderFactory */
        $downloaderFactory = Container::getContainer()->get(DownloaderFactory::class);

        try {
            $downloader = $downloaderFactory->get($sourceType);
            $downloader->download($sourcePath, $destinationPath, $downloadOptions, $output);
            $io->success('Download completed');
        } catch (\Exception $e) {
            $io->warning([$e->getMessage()]);
            $io->warning('Some issues appeared during source code update');
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
            CoreOptions::SOURCES_REUSE => CoreOptions::get(CoreOptions::SOURCES_REUSE),
            CoreOptions::SOURCES_SECONDARY_REUSE => CoreOptions::get(CoreOptions::SOURCES_SECONDARY_REUSE)
        ];
    }
}
