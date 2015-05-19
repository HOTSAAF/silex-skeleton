<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class BuildCommand extends Command
{
    private $input;
    private $output;

    private $buildDir;

    public function __construct($rootPath, $buildDir = 'build')
    {
        // Cleaning the configurations
        $this->rootPath = rtrim($rootPath, '/');
        $this->buildDir = rtrim($buildDir, '/');

        // Addig automatic configurations
        $this->buildPath = $this->rootPath . '/' . $this->buildDir;
        // Creating the main build directory.
        if (!is_dir($this->buildPath)) {
            mkdir($this->buildPath);
        }

        // Making the build_path parameter a bit prettier.
        $this->buildPath = realpath($this->buildPath);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:build')
            ->setDescription('Builds out the project.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Switching to the build/ directory
        $prevPath = trim(`pwd`);
        chdir($this->buildPath);

        // Saving the input and output variables for other methods
        $this->input = $input;
        $this->output = $output;

        $this->checkOS();

        // Git repo handling.
        $this->checkoutProject();
        $this->buildProject();

        chdir($prevPath);
    }

    protected function checkOS()
    {
        // Removed this check as long as it seems to work on Windows.
        return true;
        // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        //     $this->output->writeln("<error>Correct behavior of this command is not guaranteed under Windows, check the build result before deployment.</error>");
        // }
    }

    /**
     * Checking out the project from GIT to the `build/` folder.
     */
    protected function checkoutProject()
    {
        $gitRemoteUrl = exec('git config --get remote.origin.url');

        if (!is_dir($this->buildPath . '/.git')) {
            // If no deployment version was cloned yet, create it.
            $this->output->writeln("<info>Creating the app's deployment version into the {$this->buildPath}/ directory...</info>");
            exec('git init');
            exec("git remote add -f origin {$gitRemoteUrl}");
            exec('git pull origin master');
        } else {
            // Cleaning the existing GIT repo, and fetching the updates.
            $this->output->writeln("<info>Updating the 'build' git repository...</info>");
            exec('git clean -f');
            exec('git reset --hard');
            exec('git pull origin master');
        }
    }

    protected function buildProject()
    {
        # Remove the `web/image` softlink.
        if (is_file($this->buildPath . '/web/images')) {
            unlink($this->buildPath . '/web/images');
        }

        $this->output->writeln('<info>Generating asset versions...</info>');
        $this->updateAssetVersions();

        $this->output->writeln('<info>Installing npm modules...</info>');
        // Create a symlink, so that npm installation uses the same node_modules
        // folder each time
        if (!is_link('node_modules')) {
            symlink($this->rootPath . '/node_modules', 'node_modules');
        }
        exec('npm install --production');

        $this->output->writeln('<info>Installing bower components...</info>');
        exec('bower install');

        $this->output->writeln('<info>Building the frontend assets with Gulp...</info>');
        exec('gulp build --env=prod');

        $this->output->writeln('<info>Installing Composer dependencies...</info>');
        exec('composer install -o --no-dev --prefer-dist');

        $this->output->writeln('<info>Done building the project.</info>');
    }

    protected function updateAssetVersions()
    {
        $assetVersion = time();

        // Updating the backend "config.yml" configuration.
        $configPath = "{$this->buildPath}/src/config/config.yml";
        $config = Yaml::parse(file_get_contents($configPath));
        $config['asset_version'] = $assetVersion;
        file_put_contents($configPath, Yaml::dump($config, 2));

        // Updating the frontend "_global.scss" configuration.
        $configPath = "{$this->buildPath}/front_src/styles/config/_global.scss";
        $config = file_get_contents($configPath);
        $config = preg_replace('/(\$g-asset-version: )(\d+)/i', "\${1}$assetVersion", $config);
        file_put_contents($configPath, $config);
    }
}
