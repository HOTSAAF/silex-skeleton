<?php

namespace App\Command;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class DropDatabaseCommand extends Command
{
    const RETURN_CODE_NOT_DROP = 1;
    const RETURN_CODE_NO_FORCE = 2;

    public function __construct($config)
    {
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('database:drop')
            ->setDescription('Drops the configured databases')
            ->addOption('if-exists', null, InputOption::VALUE_NONE, 'Don\'t trigger an error, when the database doesn\'t exist')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Set this parameter to execute this action');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ifExists = $input->getOption('if-exists');
        $name = $this->config['dbname'];
        unset($this->config['dbname']);

        if ($input->getOption('force')) {
            $connection = DriverManager::getConnection($this->config);
            $shouldDropDatabase = !$ifExists && in_array($name, $connection->getSchemaManager()->listDatabases());

            $error = false;
            try {
                if ($shouldDropDatabase) {
                    $connection->getSchemaManager()->dropDatabase($name);
                    $output->writeln(sprintf('<info>Dropped database for connection named <comment>%s</comment></info>', $name));
                } else {
                    $output->writeln(sprintf('<info>Database for connection named <comment>%s</comment> doesn\'t exist. Skipped.</info>', $name));
                }
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Could not drop database for connection named <comment>%s</comment></error>', $name));
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                $error = true;
            }

            $connection->close();

            return $error ? self::RETURN_CODE_NOT_DROP : 0;

        } else {
            $output->writeln('<error>ATTENTION:</error> This operation should not be executed in a production environment.');
            $output->writeln('');
            $output->writeln(sprintf('<info>Would drop the database named <comment>%s</comment>.</info>', $name));
            $output->writeln('Please run the operation with --force to execute');
            $output->writeln('<error>All data will be lost!</error>');

            return self::RETURN_CODE_NO_FORCE;
        }
    }
}
