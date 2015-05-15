<?php

namespace App\Command;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class CreateDatabaseCommand extends Command
{

    public function __construct($config)
    {
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('database:create')
            ->setDescription('Drops the configured databases')
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Don\'t trigger an error, when the database already exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ifNotExists = $input->getOption('if-not-exists');
        $name = $this->config['dbname'];
        unset($this->config['dbname']);

        $connection = DriverManager::getConnection($this->config);
        $shouldNotCreateDatabase = $ifNotExists || in_array($name, $connection->getSchemaManager()->listDatabases());

        $error = false;

        try {
            if ($shouldNotCreateDatabase) {
                $output->writeln(sprintf('<info>Database for connection named <comment>%s</comment> already exists. Skipped.</info>', $name));
            } else {
                $connection->getSchemaManager()->createDatabase($name);
                $output->writeln(sprintf('<info>Created database for connection named <comment>%s</comment></info>', $name));
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Could not create database for connection named <comment>%s</comment></error>', $name));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }
        $connection->close();

        return $error ? 1 : 0;

    }
}
