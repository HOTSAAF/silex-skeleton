<?php

$ftpConfig = require 'ftp_config.php';
$ftpConfig = $ftpConfig[basename(__FILE__, '.php')];

return [
    'project' => [
        // '<pass>' should not contain too special characters, that would
        // require putting it in quotes. The deployment tool does not seem to
        // work with a syntax like that.
        'remote'      => "ftp://{$ftpConfig['user']}:{$ftpConfig['pass']}@{$ftpConfig['host']}/{$ftpConfig['remote_path']}",
        'local'       => 'build/',
        'passivemode' => 'yes',
        'allowdelete' => 'yes',
        'preprocess'  => 'no',
        'purge'       => ['var/cache/twig'],
        'ignore'      => '
            _ignore
            front_src
            node_modules
            web/index_dev.php

            .git*
            project.pp[jx]
            /deployment.*
            /log
            temp/*
            !temp/.htaccess
        ',
    ],
    'colors' => 'yes',
];
