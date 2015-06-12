<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use App\Util\YmlDistImplementationValidator;

/**
 * This commands checks for the existence of a remote "parameters.yml" file,
 * and if it exists, it validates it by the locally stored *dist file.
 * In case the keys differ, it outputs the `diff` commands' output.
 */
class ParameterCheckerCommand extends Command
{
    private $input;
    private $output;

    private $rootPath;
    private $remotePath;
    private $localDistPath;
    private $ftpConfig;

    private $tmpPath;
    private $tmpDir;
    private $fullTmpPath;
    private $localTmpYmlPath;
    private $localTmpDistYmlPath;

    public function __construct($rootPath, $localDistPath, $ftpConfig, $tmpPath = null, $tmpDir = 'parameter_checker')
    {
        // Cleaning the configurations
        $this->rootPath = rtrim($rootPath, '/');
        $this->ftpConfig = $ftpConfig;
        $this->remotePath = $remotePath;
        $this->localDistPath = $localDistPath;
        if ($tmpPath === null) {
            $tmpPath = $rootPath . '/.tmp';
        }
        $this->tmpPath = $tmpPath;
        $this->tmpDir = $tmpDir;
        $this->fullTmpPath = $tmpPath . '/' . $tmpDir;
        $this->localTmpYmlPath = $this->fullTmpPath . '/parameters.yml';
        $this->localTmpDistYmlPath = $this->fullTmpPath . '/parameters.yml.dist';

        parent::__construct();
    }

    protected function configure()
    {
        reset($this->ftpConfig);

        $this
            ->setName('app:check_remote_parameter')
            ->setDescription('Checks for the existence of a remote parameters.yml file for a given deployment stage. It also compares it with the local *dist file.')
            ->addArgument(
                'stage',
                InputOption::VALUE_REQUIRED,
                'The target stage to check the parameters.yml file at.',
                key($this->ftpConfig)
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Saving the input and output variables for other methods
        $this->input = $input;
        $this->output = $output;

        $stage = $input->getArgument('stage');

        // Checking the stage
        if (!isset($this->ftpConfig[$stage])) {
            throw new \RuntimeException('The given stage "' . $stage . '" is not registered in the configuration. The available stages are: "' . implode('", "', array_keys($this->ftpConfig)) . '".');
        }

        $this->prepareDirsAndFiles();

        $this->output->writeln('<info>Fetching remote parameter file...</info>');
        $this->fetchRemoteParameterFile();

        $this->output->writeln('<info>Validating remote parameter file...</info>');
        (new YmlDistImplementationValidator(
            $this->localTmpDistYmlPath,
            $this->localTmpYmlPath
        ))->validate();

        $this->output->writeln('<info>The remote parameter file seems OK.</info>');
    }

    private function prepareDirsAndFiles()
    {
        if (!is_dir($this->tmpPath)) {
            mkdir($this->tmpPath);
        }

        if (!is_dir($this->fullTmpPath)) {
            mkdir($this->fullTmpPath);
        }

        // Make stuff prettier
        $this->tmpPath = realpath($this->tmpPath);
        $this->fullTmpPath = realpath($this->fullTmpPath);

        // The ftp_get() command requires an existing file.
        file_put_contents($this->localTmpYmlPath, '');
        copy($this->localDistPath, $this->localTmpDistYmlPath);
    }

    private function fetchRemoteParameterFile()
    {
        $stage = $this->input->getArgument('stage');

        // Get ftp config based on the stage
        $ftpConfig = $this->ftpConfig[$stage];
        $remotePath = $ftpConfig['remote_path'] .'/'.$ftpConfig['parameters_yml'];

        $conn_id = ftp_connect($ftpConfig['host']);
        if (!@ftp_login($conn_id, $ftpConfig['user'], $ftpConfig['pass'])) {
            throw new \RuntimeException("FTP login was not successful while trying to check the remote parameters.yml file. Error:\n" . print_r(error_get_last(), true));
        }

        if (!@ftp_get($conn_id, $this->localTmpYmlPath, $remotePath, FTP_BINARY)) {
            throw new \RuntimeException('It seems that the remote "' . basename($this->remotePath) . "\" file is not present. Implement it by the available *dist file. Error:\n" . print_r(error_get_last(), true));
        }
    }
}
