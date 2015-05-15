<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
// use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

class DeployCommand extends Command
{
    private $rootPath;
    private $configPath;
    private $availableStages;
    private $defaultStage;

    public function __construct($rootPath, $configPath, $defaultStage = null)
    {
        // Cleaning the configurations
        $this->rootPath = rtrim($rootPath, '/');
        $this->configPath = rtrim($configPath, '/');
        $this->defaultStage = $defaultStage;

        // Getting the available stages from deployment configurations.
        $ftpConfig = require $configPath . '/ftp_config.php';
        $this->availableStages = array_keys($ftpConfig);

        if ($this->defaultStage === null) {
            $this->defaultStage = $this->availableStages[0];
        }

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:deploy')
            ->setDescription('Builds out the project.')
            ->addArgument(
                'stage',
                InputOption::VALUE_REQUIRED,
                'The target stage to deploy to.',
                $this->defaultStage
            )
            ->addOption(
                'no-build',
                null,
                InputOption::VALUE_NONE,
                'If given, no building will take place.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        chdir($this->rootPath);

        // Getting and checking the stage argument.
        $stage = $input->getArgument('stage');
        $stage_conf_path = "{$this->configPath}/${stage}.php";
        if (!is_file($stage_conf_path)) {
            throw new \Exception("The given stage \"$stage\" does not seem to exist. Could it be that you forgot to implement the *.dist file? Tried to load the following: \"$stage_conf_path\".");
        }

        // Checking the remote parameters.yml file
        $command = $this->getApplication()->find('app:check_remote_parameter');
        // $localInput = new ArgvInput(['my:check_remote_parameter', $stage]);
        $localInput = new ArrayInput([$stage]);
        $localOutput = new ConsoleOutput();
        $command->run($localInput, $localOutput);
        // Continue if no exceptions were thrown

        // Running the build command.
        if (!$input->getOption('no-build')) {
            $command = $this->getApplication()->find('app:build');
            $localInput = new ArrayInput(['app:build']);
            $localOutput = new ConsoleOutput();
            $command->run($localInput, $localOutput);
            chdir($this->rootPath); // Safety measure
        }

        // Running deployment.
        $output->writeln("<info>Running deployment to the \"$stage\" stage. (with `dg/ftp-deployment`)</info>");

        $process = new Process("{$this->rootPath}/vendor/bin/deployment $stage_conf_path");
        $process->setTimeout(0);

        // The following can throw a ProcessFailedException
        $process->mustRun(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->writeln("<error>$buffer</error>");
            } else {
                $output->writeln("$buffer");
            }
        });
    }
}
