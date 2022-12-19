<?php

const EXTRACT_DIRECTORY = "cache/extractedComposer";

if (!file_exists(EXTRACT_DIRECTORY . '/vendor/autoload.php')) {
    $composerPhar = new Phar("bin/composer.phar");
    $composerPhar->extractTo(EXTRACT_DIRECTORY);
}

require_once(EXTRACT_DIRECTORY . '/vendor/autoload.php');

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput as Output;
use Symfony\Component\Console\Output\OutputInterface;

$isDebug = isset($_GET['debug']);

putenv('COMPOSER_HOME=' . __DIR__ . '/vendor/bin/composer');

$output = new Output(
    $isDebug ? OutputInterface::VERBOSITY_DEBUG : OutputInterface::VERBOSITY_NORMAL
);

$input = new ArrayInput(['command' => 'update', '--no-dev' => true]);
$application = new Application();
$application->setAutoExit(false);
$application->run($input, $output);

if ($isDebug) {
    echo '<pre>' . $output->fetch() . '</pre>';
}

function deleteDirectory($path)
{
    $files = array_diff(scandir($path), ['.', '..']);

    foreach ($files as $file) {
        (is_dir("$path/$file")) ? deleteDirectory("$path/$file") : unlink("$path/$file");
    }

    return rmdir($path);
}

deleteDirectory(EXTRACT_DIRECTORY);