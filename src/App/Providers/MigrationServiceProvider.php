<?php
namespace App\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class MigrationServiceProvider implements ServiceProviderInterface
{
    /**
     * The console containerlication.
     *
     * @var Console
     */
    protected $console;

    public function __construct(Console $console)
    {
        $this->console = $console;
    }

    public function register(Container $container)
    {
        $container['doctrine.migrations.table_name'] = 'migration_versions';
        $container['doctrine.migrations.name'] = 'Application Migrations';

        $container['doctrine.migrations.output_writer'] = new OutputWriter(
            function ($message) {
                $output = new ConsoleOutput();
                $output->writeln($message);
            }
        );

        $container['doctrine.migrations.namespace'] = function($container) {
            return (isset($container['migrations.namespace']))? $container['migrations.namespace']:null;
        };

        $container['doctrine.migrations.path'] = function($container) {
            return (isset($container['migrations.path']))? $container['migrations.path']:null;
        };


        $helperSet = new HelperSet(array(
            'connection' => new ConnectionHelper($container['doctrine']->getConnection()),
            'dialog'     => new DialogHelper(),
        ));

        if (isset($container['orm.em'])) {
            $helperSet->set(new EntityManagerHelper($container['orm.em']), 'em');
        }

        $this->console->setHelperSet($helperSet);

        $commands = array(
            'Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand'
        );

        // @codeCoverageIgnoreStart
        if (true === $this->console->getHelperSet()->has('em')) {
            $commands[] = 'Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand';
        }

        // @codeCoverageIgnoreEnd
        $configuration = new Configuration($container['doctrine']->getConnection(), $container['doctrine.migrations.output_writer']);
        $configuration->setMigrationsDirectory($container['doctrine.migrations.path']);
        $configuration->setName($container['doctrine.migrations.name']);
        $configuration->setMigrationsNamespace($container['doctrine.migrations.namespace']);
        $configuration->setMigrationsTableName($container['doctrine.migrations.table_name']);
        $configuration->setMigrationsDirectory($container['doctrine.migrations.path']);
        // $configuration->registerMigrationsFromDirectory($container['doctrine.migrations.path']);

        foreach ($commands as $name) {
            $command = new $name();
            $command->setMigrationConfiguration($configuration);
            $this->console->add($command);
        }
    }
}
