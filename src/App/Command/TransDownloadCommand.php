<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use ZeeCoder\OneSky\Helper as OneSkyHelper;

class TransDownloadCommand extends Command
{
    private $appRootPath;
    private $oneSkyHelperConfig;
    private $oneSkyCommandConfig;

    public function __construct($appRootPath, $oneSkyHelperConfig, $oneSkyCommandConfig)
    {
        parent::__construct();

        $this->appRootPath = $appRootPath;
        $this->oneSkyHelperConfig = $oneSkyHelperConfig;
        $this->oneSkyCommandConfig = $oneSkyCommandConfig;
    }

    protected function configure()
    {
        $this
            ->setName("app:trans:download")
            ->setDescription('Downloads the project\'s translation files from OneSky. It can optionally overwrite the existing local translations.')
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'If given, the local translations will be overwritten by the ones in the OneSky project.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $overwriteOption = $input->getOption('overwrite');

        // Show a warning if needed.
        if ($overwriteOption) {
            $helper = new QuestionHelper();
            $question = new ConfirmationQuestion('<question>Are you sure you want to overwrite your local translation files with the ones stored in OneSky? (y/n)</question> ', false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $output->writeln('Downloading files' . ($overwriteOption ? ', overwriting the local translations': '') . '...');

        $localePathPrefix = $overwriteOption ? '' : $this->oneSkyCommandConfig['locale_dir_prefix'];
        $output->writeln('Target directories: "' . ($this->oneSkyCommandConfig['local_download_path'] . '/' . $localePathPrefix) . '<locale>' . '".');

        $oneSkyHelper = new OneSkyHelper($this->oneSkyHelperConfig);

        // Getting a list of the translation files
        $filenames = $oneSkyHelper->getProjectTranslationFileNames();

        // Getting all the locales
        $locales = $oneSkyHelper->getProjectLocaleCodes();

        // Creating the locale folders
        foreach ($locales as $localeCode) {
            $dirPath = $this->appRootPath . '/' . $this->oneSkyCommandConfig['local_download_path'] . '/' . $localePathPrefix . $localeCode;
            if (!is_dir($dirPath)) {
                mkdir($dirPath);
            }
        }

        // Downloading all the translation files for every locale
        foreach ($locales as $locale) {
            foreach ($filenames as $filename) {
                $translationFileContent = $oneSkyHelper->getTranslationFile($filename, $locale);

                file_put_contents(
                    $this->oneSkyCommandConfig['local_download_path'] . '/' . $localePathPrefix . $locale . '/' . $filename,
                    $translationFileContent
                );
            }
        }

        $output->writeln('Finished downloading the files.');
    }
}
