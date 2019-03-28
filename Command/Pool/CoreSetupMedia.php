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
use CoreDevBoxScripts\Library\Directory;
use CoreDevBoxScripts\Library\JsonConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for Core final steps
 */
class CoreSetupMedia extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('core:setup:media')
            ->setDescription('Fetch / Update Media Files')
            ->setHelp('Update Media Files');

        $this->questionOnRepeat = 'Try to update media again?';

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->executeRepeatedly('updateMedia', $input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function updateMedia(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->commandTitle($io, 'Media Files sync.');

        $useExistingSources = $this->requestOption(CoreOptions::MEDIA_DOWNLOAD, $input, $output, true);
        if (!$useExistingSources) {
            $output->writeln('<comment>Step skipped</comment>');
            return true;
        }

        $sourceType = JsonConfig::getConfig('sources->media->source_type');
        $source = JsonConfig::getConfig('sources->media->source_path');
        $sourceBranch = JsonConfig::getConfig('sources->media->source_branch');
        $destinationPath = JsonConfig::getConfig('sources->media->local_temp_path');
        $destinationMediaPath = JsonConfig::getConfig('sources->media->local_media_website_path');
        $downloadOptions = JsonConfig::getConfig('sources->media');

        $headers = ['Parameter', 'Value'];
        $rows = [
            ['Media Source Type', $sourceType],
            ['Media Source', $source],
            ['Media Branch', $sourceBranch],
            ['Temp folder', $destinationPath],
            ['Project Media Folder', $destinationMediaPath],
        ];
        $io->table($headers, $rows);

        $command = "mkdir -p " . $destinationPath;
        $this->executeCommands(
            $command,
            $output
        );

        if (!Directory::isEmptyDir($destinationPath)) {
            $command = "rm -fr $destinationPath/*";
            $output->writeln("<comment>Command to clear directory : $command</comment>");
            $clearTempMedia = $this->requestOption(CoreOptions::MEDIA_CLEAR_TEMP_LOCAL, $input, $output, true);

            if ($clearTempMedia) {
                $this->executeCommands(
                    $command,
                    $output
                );
            }
        }

        /** @var DownloaderFactory $downloaderFactory */
        $downloaderFactory = Container::getContainer()->get(DownloaderFactory::class);

        try {

            if ($sourceType != 'vcs') {
                $destinationPath = $destinationPath . DIRECTORY_SEPARATOR . basename($source);
            }

            $downloader = $downloaderFactory->get($sourceType);
            $downloader->download($source, $destinationPath, $downloadOptions, $output);
        } catch (\Exception $e) {
            $io->warning([$e->getMessage()]);
            $io->warning('Some issues appeared during media update');
            return false;
        }

        try {
            $newPath = $this->unGz($destinationPath, $output);
            if (!is_file($newPath) && !is_dir($newPath)) {
                throw new \Exception('File is not exists. Path: ' . $newPath);
            }
            $destinationPath = $newPath;
        } catch (\Exception $e) {
            $io->note($e->getMessage());
            return false;
        }

        try {
            $output->writeln('Check and create media folder in website folder if doesn\'t exist');
            $command = "mkdir -p " . $destinationMediaPath;
            $this->executeCommands(
                $command,
                $output
            );

            $output->writeln(
                'Copying files from ' . $destinationPath . ' to ' . $destinationMediaPath . ' folder...'
            );
            $command = "cp -rf $destinationPath/* $destinationMediaPath";

            $this->executeCommands(
                $command,
                $output
            );
        } catch (\Exception $e) {
            $io->note($e->getMessage());
            $io->note('Step skipped. Not possible to continue with media updating');
            return false;
        }

        if (!isset($e)) {
            $io->success('Media has been updated');
        } else {
            $io->warning('Some issues appeared during Media updating');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsConfig()
    {
        return [
            CoreOptions::MEDIA_CLEAR_TEMP_LOCAL => CoreOptions::get(CoreOptions::MEDIA_CLEAR_TEMP_LOCAL),
            CoreOptions::MEDIA_DOWNLOAD => CoreOptions::get(CoreOptions::MEDIA_DOWNLOAD)
        ];
    }
}
