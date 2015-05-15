<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SetupProjectCommand extends Command {

    // Doctrine EntityManager
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:setup')
            ->addOption(
                'with-tests',
                false,
                InputOption::VALUE_NONE,
                'The database will be populated with test data.'
            )
            ->addOption(
                'no-build',
                false,
                InputOption::VALUE_NONE,
                "If given, no building will take place."
            )
            ->setDescription('Sets up the project.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '-1');

        $application = $this->getApplication();
        $application->setAutoExit(false);

        $output->writeln('<comment>An existing database will be dropped first</comment>');
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion('<question>Are you sure you want to drop an existing database? (y/n)</question> ', true);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $stopwatch = new Stopwatch();
        $stopwatch->start('app_setup');

        // Dropping the database
        $application->run(
            new StringInput('database:drop --force'),
            $output
        );

        // Creating the database
        $application->run(
            new StringInput('database:create'),
            $output
        );

        // Updateing database schema
        $application->run(
            new StringInput('orm:schema-tool:update --force'),
            $output
        );

        // Collecting alice data (Assets and optionally tests.)
        $output->writeln('Loading alice fixture files...');
        $files = glob(__DIR__.'/../DataFixtures/Data/Asset/*');
        if ($input->getOption('with-tests')) {
            $output->writeln('Loading test alice fixture files...');
            $testFiles = glob(__DIR__.'/../DataFixtures/Data/Test/*');
            $files = array_merge($files, $testFiles);
        }

        $output->writeln('Persisting entities...');
        $loader = new \Nelmio\Alice\Fixtures($this->em);
        $objects = $loader->loadFiles($files);

        // Build project
        if (!$input->getOption('no-build')) {
            $output->writeln('<info>Installing npm modules...</info>');
            exec('npm install');

            $output->writeln('<info>Installing bower components...</info>');
            exec('bower install');

            $output->writeln('<info>Building the frontend assets via Gulp...</info>');
            exec('gulp build');

            $output->writeln('<info>Done building the project.</info>');
        }

        // All done, summarising.
        $stopwatchEvent = $stopwatch->stop('app_setup');
        $setupDuration = $stopwatchEvent->getDuration();

        $output->writeln("\n<info>Done setting up project.</info>");
        if ($setupDuration > 60000) {
            $output->writeln("<info>Elapsed time: ".($setupDuration / 1000 / 60)." minutes.</info>");
        } else {
            $output->writeln("<info>Elapsed time: ".($setupDuration / 1000)." seconds.</info>");
        }
    }

}
