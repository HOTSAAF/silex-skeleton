<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class SlackNotifCommand extends Command
{
    private $input;
    private $output;
    private $config;

    public function __construct($config)
    {
        $neededKeys = ['token', 'channel'];

        $defaultConfig = [
            'icon_emoji' => ':thumbsup:',
            'username' => 'Deployer-bot',
        ];
        $this->config = array_merge($defaultConfig, $config);

        $diff = array_diff($neededKeys, array_keys($config));
        if (0 !== count($diff)) {
            throw new \RuntimeException('The following mandatory configuration parameters are missing for the SlackNotifCommand: "' . implode('", "', $diff) . '"');
        }

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:slack:notif')
            ->setDescription('Pushes a notification to slack.')
            ->addOption(
                'text',
                't',
                InputOption::VALUE_REQUIRED,
                'The text the slack message will have.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = $input->getOption('text');
        if ($text === null) {
            throw new \RuntimeException('The "text" option is required.');
        }

        $url =
            'https://slack.com/api/chat.postMessage?' .
            http_build_query([
                'token' => $this->config['token'],
                'channel' => '#' . $this->config['channel'],
                'icon_emoji' => $this->config['icon_emoji'],
                'username' => $this->config['username'],
                'text' => $text,
            ]);
        $result = @file_get_contents($url);
        if (!$result) {
            throw new \RuntimeException(print_r(error_get_last(), true));
        }

        $result = json_decode($result, true);
        if ($result['ok'] !== true) {
            throw new \RuntimeException('Slack error code: "' . $result['error'] . '".');
        }
    }
}
