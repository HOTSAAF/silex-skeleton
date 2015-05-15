<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceOffCommand extends Command
{
    private $ftpConfig;
    private $availableStages;
    private $defaultStage;

    public function __construct($ftpConfig, $defaultStage = null)
    {
        // Cleaning the configurations
        $this->ftpConfig = $ftpConfig;
        $this->defaultStage = $defaultStage;

        // Getting the available stages from deployment configurations.
        $this->availableStages = array_keys($this->ftpConfig);

        if ($this->defaultStage === null) {
            $this->defaultStage = $this->availableStages[0];
        }

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:maintenance:off')
            ->setDescription('Switches off the maintenance mode.')
            ->addArgument(
                'stage',
                InputOption::VALUE_REQUIRED,
                'The target stage where maintenance will be switched off.',
                $this->defaultStage
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stage = $input->getArgument('stage');

        // Get ftp config based on the stage
        $ftpConfig = $this->ftpConfig[$stage];

        $conn_id = ftp_connect($ftpConfig['host']);
        if (!@ftp_login($conn_id, $ftpConfig['user'], $ftpConfig['pass'])) {
            throw new \RuntimeException("FTP login was not successful. Error:\n" . print_r(error_get_last(), true));
        }

        $remoteMaintenanceFilePath = trim($ftpConfig['remote_path'], '/') . '/maintenance.on';

        if (@ftp_size($conn_id, $remoteMaintenanceFilePath) !== -1) { // Checking for the existence of the maintenance file
            if (!@ftp_delete($conn_id, $remoteMaintenanceFilePath)) {
                throw new \RuntimeException("Error:\n" . print_r(error_get_last(), true));
            }
        }

        $output->writeln('<info>Maintenance mode was succesfully switched off.</info>');
    }
}
