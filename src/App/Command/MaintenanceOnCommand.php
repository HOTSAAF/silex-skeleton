<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceOnCommand extends Command
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
            ->setName('app:maintenance:on')
            ->setDescription('Switches on the maintenance mode.')
            ->addArgument(
                'stage',
                InputOption::VALUE_REQUIRED,
                'The target stage where maintenance will be switched on.',
                $this->defaultStage
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stage = $input->getArgument('stage');

        // Get ftp config based on the stage
        $ftpConfig = $this->ftpConfig[$stage];

        $result = @file_put_contents(
            'ftp://' . $ftpConfig['user'] . ':' . $ftpConfig['pass'] . '@' . $ftpConfig['host'] . '/' . trim($ftpConfig['remote_path'], '/') . '/maintenance.on',
            ''
            ,
            0,
            @stream_context_create(['ftp' => ['overwrite' => true]])
        );

        $errors = error_get_last();

        if ($errors === null) {
            $output->writeln('<info>Maintenance mode was succesfully switched on.</info>');
        } else {
            throw new \RuntimeException("It seems that one or more error occured:\n" . print_r($errors, true));
        }
    }
}
