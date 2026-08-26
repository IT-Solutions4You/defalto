<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Composer_Model
{
    public static function installDependencies(string $projectDirectory): array
    {
        $projectDirectory = rtrim($projectDirectory, '/\\');
        $extractDirectory = $projectDirectory . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'extractedComposer';
        $composerHome = $projectDirectory . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin'
            . DIRECTORY_SEPARATOR . 'composer';
        $composerCache = $projectDirectory . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin'
            . DIRECTORY_SEPARATOR . 'composer-cache';
        $previousComposerHome = getenv('COMPOSER_HOME');
        $previousComposerCache = getenv('COMPOSER_CACHE_DIR');

        try {
            $autoloadFile = $extractDirectory . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

            if (!is_file($autoloadFile)) {
                self::removeDirectory($extractDirectory);
                $composerPhar = new Phar(
                    $projectDirectory . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'composer.phar'
                );
                $composerPhar->extractTo($extractDirectory);
            }

            require_once $autoloadFile;
            putenv('COMPOSER_HOME=' . $composerHome);
            putenv('COMPOSER_CACHE_DIR=' . $composerCache);
            $isDebug = isset($_GET['debug']) || (function_exists('vglobal') && (bool)vglobal('debug'));
            $output = new Symfony\Component\Console\Output\BufferedOutput(
                $isDebug
                    ? Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG
                    : Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL
            );
            $input = new Symfony\Component\Console\Input\ArrayInput([
                'command' => 'install',
                '--no-dev' => true,
                '--prefer-dist' => true,
                '--no-progress' => true,
                '--optimize-autoloader' => true,
            ]);
            $application = new Composer\Console\Application();
            $application->setAutoExit(false);
            $exitCode = $application->run($input, $output);

            return [
                'exit_code' => $exitCode,
                'output' => $output->fetch(),
            ];
        } finally {
            false === $previousComposerHome
                ? putenv('COMPOSER_HOME')
                : putenv('COMPOSER_HOME=' . $previousComposerHome);
            false === $previousComposerCache
                ? putenv('COMPOSER_CACHE_DIR')
                : putenv('COMPOSER_CACHE_DIR=' . $previousComposerCache);
            self::removeDirectory($extractDirectory);
            self::removeDirectory($composerHome);
            self::removeDirectory($composerCache);
        }
    }

    protected static function removeDirectory(string $directory): void
    {
        if (is_link($directory)) {
            @unlink($directory);

            return;
        }

        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isLink() || $item->isFile()) {
                @unlink($item->getPathname());
            } else {
                @rmdir($item->getPathname());
            }
        }

        @rmdir($directory);
    }
}
