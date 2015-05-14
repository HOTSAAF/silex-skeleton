<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateAdminpassCommand extends Command {
    // $container;

    // public function __construct($container)
    // {
    //     $this->container = $container;
    // }

    protected function configure()
    {
        $this
            ->setName("generate:adminpass")
            ->setDescription('Converts the given raw password to a hash value, which can be used in the firewall configuration.')
            ->addArgument(
                'rawPass',
                InputOption::VALUE_NONE,
                'The raw password which will be converted.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rawPass = $input->getArgument('rawPass');
        $output->writeln(
            'The hash for the raw password "' . $rawPass . '" is the following: "' .
            (new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder())->encodePassword($rawPass, '') .
            '".'
        );
    }
}
