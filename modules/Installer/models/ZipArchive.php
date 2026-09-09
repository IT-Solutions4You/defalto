<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
class Installer_ZipArchive_Model extends ZipArchive
{
    public const MAX_FILES = 10000;
    public const MAX_UNCOMPRESSED_SIZE = 209715200;

    public static array $skipFiles = ['.env', 'config.inc.php', 'composer.lock', 'index.php', 'install.php', 'manifest.xml', 'parent_tabdata.php', 'tabdata.php', 'update.php', 'vtigercron.sh'];
    public static array $skipFolders = [
        'user_privileges',
        'manifest',
        'update',
        'icons',
        'installer',
        'cache/installer',
        '.git',
        '.agents',
        '.codex',
    ];
    public static array $errors = [];

    protected array $allowedRoots = [];
    protected array $copiedFiles = [];
    protected array $skippedFiles = [];
    protected array $backups = [];
    protected array $newFiles = [];
    protected array $createdDirectories = [];

    public function setAllowedRoots(array $roots): void
    {
        $this->allowedRoots = array_values(array_unique(array_filter(array_map(
            static fn($root) => trim(str_replace('\\', '/', (string)$root), '/'),
            $roots
        ))));
    }

    public function resolveSourceFolder(string $expectedFolder): string
    {
        $expectedFolder = trim(str_replace('\\', '/', $expectedFolder), '/');
        $expectedPrefix = $expectedFolder === '' ? '' : $expectedFolder . '/';
        $topLevelFolders = [];

        for ($index = 0; $index < $this->numFiles; $index++) {
            $filename = $this->getNameIndex($index);

            if (!is_string($filename) || $filename === '' || str_contains($filename, "\0")) {
                continue;
            }

            $filename = ltrim(str_replace('\\', '/', $filename), '/');

            if ($expectedPrefix === '' || str_starts_with($filename, $expectedPrefix)) {
                return $expectedFolder;
            }

            $separatorPosition = strpos($filename, '/');

            if (false !== $separatorPosition) {
                $topLevelFolder = substr($filename, 0, $separatorPosition);

                if ($topLevelFolder !== '' && $topLevelFolder !== '.' && $topLevelFolder !== '..') {
                    $topLevelFolders[$topLevelFolder] = true;
                }
            }
        }

        if (count($topLevelFolders) === 1) {
            return (string)array_key_first($topLevelFolders);
        }

        return $expectedFolder;
    }

    /**
     * Stages, validates and copies package files. Existing files are backed up
     * until commitFiles() is called, so a later module installation failure can
     * restore the previous application files.
     *
     * @return array<string>
     */
    public function extractSubDirTo(string $destination, string $zipSubDir, string $workspace = ''): array
    {
        self::$errors = [];
        $this->copiedFiles = [];
        $this->skippedFiles = [];
        $this->backups = [];
        $this->newFiles = [];
        $this->createdDirectories = [];

        if ($this->numFiles > self::MAX_FILES) {
            throw new RuntimeException('ZIP contains too many entries');
        }

        $destination = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $destination), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR;
        $zipSubDir = trim(str_replace('\\', '/', $zipSubDir), '/');
        $prefix = $zipSubDir === '' ? '' : $zipSubDir . '/';
        $workspace = $workspace !== '' ? $workspace : sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'defalto-installer-' . bin2hex(random_bytes(6));
        $stageDirectory = $workspace . DIRECTORY_SEPARATOR . 'staging';
        $backupDirectory = $workspace . DIRECTORY_SEPARATOR . 'backup';

        $this->createDirectory($stageDirectory);
        $this->createDirectory($backupDirectory);
        $stagedFiles = [];
        $stagedPathKeys = [];
        $totalSize = 0;

        try {
            for ($index = 0; $index < $this->numFiles; $index++) {
                $filename = $this->getNameIndex($index);

                if (!is_string($filename) || $filename === '' || str_contains($filename, "\0")) {
                    throw new RuntimeException('ZIP contains an invalid entry name');
                }

                $normalizedFilename = str_replace('\\', '/', $filename);

                if ($prefix !== '' && !str_starts_with($normalizedFilename, $prefix)) {
                    continue;
                }

                $relativePath = $prefix === '' ? $normalizedFilename : substr($normalizedFilename, strlen($prefix));

                if ($relativePath === '' || str_ends_with($relativePath, '/')) {
                    continue;
                }

                $relativePath = $this->validateRelativePath($relativePath);

                if ($this->shouldSkip($relativePath)) {
                    $this->skippedFiles[] = $relativePath;
                    Core_Install_Model::logInfo('Skipped file: ' . $this->escapeLogValue($relativePath));
                    continue;
                }

                if (!$this->isAllowedRoot($relativePath)) {
                    throw new RuntimeException('ZIP entry is outside allowed package roots: ' . $this->escapeLogValue($relativePath));
                }

                $pathKey = DIRECTORY_SEPARATOR === '\\' ? strtolower($relativePath) : $relativePath;

                if (isset($stagedPathKeys[$pathKey])) {
                    throw new RuntimeException('ZIP contains a duplicate target path: '
                        . $this->escapeLogValue($relativePath));
                }

                $stagedPathKeys[$pathKey] = true;

                $stageFile = $stageDirectory . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                $this->createDirectory(dirname($stageFile));
                $this->stageEntry($index, $normalizedFilename, $relativePath, $stageFile, $totalSize);

                $stagedFiles[$relativePath] = $stageFile;
            }

            $this->validateTargets($destination, $stagedFiles);

            foreach ($stagedFiles as $relativePath => $stageFile) {
                $this->applyFile($destination, $backupDirectory, $relativePath, $stageFile);
            }
        } catch (Throwable $throwable) {
            self::$errors[] = $throwable->getMessage();
            Core_Install_Model::logError($throwable->getMessage());
            $this->rollbackFiles();
        }

        Core_Install_Model::logInfo('Copied files total: ' . count($this->copiedFiles));
        Core_Install_Model::logInfo('Skipped files total: ' . count($this->skippedFiles));

        return self::$errors;
    }

    protected function stageEntry(
        int $index,
        string $filename,
        string $relativePath,
        string $stageFile,
        int &$totalSize
    ): void {
        if (method_exists($this, 'getStream')) {
            $source = $this->getStream($filename);

            if (!is_resource($source)) {
                throw new RuntimeException('Unable to read ZIP entry: ' . $this->escapeLogValue($relativePath));
            }

            $target = @fopen($stageFile, 'wb');

            if (!is_resource($target)) {
                fclose($source);
                throw new RuntimeException('Unable to stage ZIP entry: ' . $this->escapeLogValue($relativePath));
            }

            try {
                while (!feof($source)) {
                    $chunk = fread($source, 1048576);

                    if (false === $chunk) {
                        throw new RuntimeException('Unable to read ZIP entry: ' . $this->escapeLogValue($relativePath));
                    }

                    $totalSize += strlen($chunk);
                    $this->validateUncompressedSize($totalSize);

                    if ($chunk !== '' && strlen($chunk) !== fwrite($target, $chunk)) {
                        throw new RuntimeException('Unable to stage ZIP entry: ' . $this->escapeLogValue($relativePath));
                    }
                }
            } finally {
                fclose($source);
                fclose($target);
            }

            return;
        }

        $fileContent = $this->getFromIndex($index);

        if (false === $fileContent) {
            throw new RuntimeException('Unable to read ZIP entry: ' . $this->escapeLogValue($relativePath));
        }

        $totalSize += strlen($fileContent);
        $this->validateUncompressedSize($totalSize);

        if (strlen($fileContent) !== file_put_contents($stageFile, $fileContent, LOCK_EX)) {
            throw new RuntimeException('Unable to stage ZIP entry: ' . $this->escapeLogValue($relativePath));
        }
    }

    protected function validateUncompressedSize(int $totalSize): void
    {
        if ($totalSize > self::MAX_UNCOMPRESSED_SIZE) {
            throw new RuntimeException('ZIP uncompressed content exceeds the maximum allowed size');
        }
    }

    protected function validateRelativePath(string $relativePath): string
    {
        if (str_starts_with($relativePath, '/') || preg_match('/^[A-Za-z]:\//', $relativePath)) {
            throw new RuntimeException('ZIP contains an absolute path');
        }

        $segments = explode('/', $relativePath);

        foreach ($segments as $segment) {
            $deviceName = strtoupper(explode('.', $segment, 2)[0]);

            if ($segment === ''
                || $segment === '.'
                || $segment === '..'
                || preg_match('/[<>:"|?*\x00-\x1F]/', $segment)
                || rtrim($segment, '. ') !== $segment
                || preg_match('/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/', $deviceName)
            ) {
                throw new RuntimeException('ZIP contains an unsafe path: ' . $this->escapeLogValue($relativePath));
            }
        }

        return implode('/', $segments);
    }

    protected function isAllowedRoot(string $relativePath): bool
    {
        if (!$this->allowedRoots) {
            return true;
        }

        foreach ($this->allowedRoots as $root) {
            if ($relativePath === $root || str_starts_with($relativePath, $root . '/')) {
                return true;
            }
        }

        return false;
    }

    protected function shouldSkip(string $relativePath): bool
    {
        foreach (self::$skipFolders as $skipFolder) {
            $skipFolder = trim(str_replace('\\', '/', $skipFolder), '/');

            if ($relativePath === $skipFolder || str_starts_with($relativePath, $skipFolder . '/')) {
                return true;
            }
        }

        return in_array(basename($relativePath), self::$skipFiles, true);
    }

    protected function validateTargets(string $destination, array $stagedFiles): void
    {
        $destinationRealPath = realpath(rtrim($destination, DIRECTORY_SEPARATOR));

        if (false === $destinationRealPath) {
            throw new RuntimeException('Installer destination does not exist');
        }

        foreach ($stagedFiles as $relativePath => $stageFile) {
            $targetFile = $destination . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $parent = dirname($targetFile);
            $existingParent = $parent;

            $pathToCheck = rtrim($destination, DIRECTORY_SEPARATOR);

            foreach (explode('/', $relativePath) as $segment) {
                $pathToCheck .= DIRECTORY_SEPARATOR . $segment;

                if (is_link($pathToCheck)) {
                    throw new RuntimeException('Symbolic links are not allowed in package target paths: '
                        . $this->escapeLogValue($relativePath));
                }
            }

            while (!file_exists($existingParent) && dirname($existingParent) !== $existingParent) {
                $existingParent = dirname($existingParent);
            }

            $existingParentRealPath = realpath($existingParent);

            if (false === $existingParentRealPath
                || !$this->isPathWithinDestination($existingParentRealPath, $destinationRealPath)
            ) {
                throw new RuntimeException('Package target resolves outside the application root: '
                    . $this->escapeLogValue($relativePath));
            }

            if (is_dir($targetFile)) {
                throw new RuntimeException('A directory conflicts with package file: ' . $this->escapeLogValue($relativePath));
            }

            if ((is_file($targetFile) && !is_writable($targetFile)) || !is_writable($existingParent)) {
                throw new RuntimeException('Target is not writable: ' . $this->escapeLogValue($relativePath));
            }

            $freeSpace = @disk_free_space($existingParent);

            if (false !== $freeSpace && $freeSpace < filesize($stageFile) * 2) {
                throw new RuntimeException('Insufficient disk space for: ' . $this->escapeLogValue($relativePath));
            }
        }
    }

    protected function isPathWithinDestination(string $path, string $destination): bool
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $destination = rtrim(str_replace('\\', '/', $destination), '/');

        if (DIRECTORY_SEPARATOR === '\\') {
            $path = strtolower($path);
            $destination = strtolower($destination);
        }

        return $path === $destination || str_starts_with($path, $destination . '/');
    }

    protected function applyFile(
        string $destination,
        string $backupDirectory,
        string $relativePath,
        string $stageFile
    ): void {
        $targetFile = $destination . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $targetDirectory = dirname($targetFile);
        $this->createTargetDirectory($targetDirectory, $destination);
        $existingPermissions = is_file($targetFile) ? fileperms($targetFile) & 0777 : null;

        if (is_file($targetFile)) {
            $backupFile = $backupDirectory . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $this->createDirectory(dirname($backupFile));

            if (!copy($targetFile, $backupFile)) {
                throw new RuntimeException('Unable to back up existing file: ' . $this->escapeLogValue($relativePath));
            }

            $this->backups[$targetFile] = $backupFile;
            Core_Install_Model::logInfo('Updated file: ' . $this->escapeLogValue($relativePath));
        } else {
            $this->newFiles[] = $targetFile;
            Core_Install_Model::logInfo('New file: ' . $this->escapeLogValue($relativePath));
        }

        $temporaryTarget = $targetDirectory . DIRECTORY_SEPARATOR . '.installer-'
            . bin2hex(random_bytes(6)) . '.tmp';

        if (!copy($stageFile, $temporaryTarget)) {
            throw new RuntimeException('Unable to prepare target file: ' . $this->escapeLogValue($relativePath));
        }

        if (null !== $existingPermissions) {
            @chmod($temporaryTarget, $existingPermissions);
        } else {
            @chmod($temporaryTarget, 0644);
        }

        $this->copiedFiles[] = $targetFile;

        if (is_file($targetFile) && !unlink($targetFile)) {
            @unlink($temporaryTarget);
            throw new RuntimeException('Unable to replace target file: ' . $this->escapeLogValue($relativePath));
        }

        if (!rename($temporaryTarget, $targetFile)) {
            @unlink($temporaryTarget);
            throw new RuntimeException('Unable to finalize target file: ' . $this->escapeLogValue($relativePath));
        }

        clearstatcache(true, $targetFile);
        $expectedSize = filesize($stageFile);

        if (!is_file($targetFile)
            || filesize($targetFile) !== $expectedSize
            || hash_file('sha256', $targetFile) !== hash_file('sha256', $stageFile)
        ) {
            throw new RuntimeException('Copied file verification failed: ' . $this->escapeLogValue($relativePath));
        }

        Core_Install_Model::logSuccess(
            'Copied file: ' . $this->escapeLogValue($relativePath) . ' (' . $expectedSize . ' bytes)'
        );

        if (str_ends_with(strtolower($targetFile), '.php') && function_exists('opcache_invalidate')) {
            @opcache_invalidate($targetFile, true);
        }
    }

    protected function createDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create installer directory');
        }
    }

    protected function createTargetDirectory(string $directory, string $destination): void
    {
        $missingDirectories = [];
        $currentDirectory = $directory;
        $destination = rtrim($destination, DIRECTORY_SEPARATOR);

        while (!is_dir($currentDirectory) && $currentDirectory !== $destination) {
            $missingDirectories[] = $currentDirectory;
            $currentDirectory = dirname($currentDirectory);
        }

        $this->createDirectory($directory);

        foreach (array_reverse($missingDirectories) as $createdDirectory) {
            $this->createdDirectories[] = $createdDirectory;
        }
    }

    public function rollbackFiles(): void
    {
        $restoredFiles = 0;
        $restoreErrors = 0;

        foreach (array_reverse($this->copiedFiles) as $targetFile) {
            if (isset($this->backups[$targetFile])) {
                $backupFile = $this->backups[$targetFile];
                $restored = @copy($backupFile, $targetFile);
                clearstatcache(true, $targetFile);
                $restored = $restored
                    && is_file($targetFile)
                    && hash_file('sha256', $backupFile) === hash_file('sha256', $targetFile);
            } elseif (in_array($targetFile, $this->newFiles, true)) {
                $restored = !is_file($targetFile) || @unlink($targetFile);
            } else {
                $restored = true;
            }

            if ($restored) {
                $restoredFiles++;
            } else {
                $restoreErrors++;
                Core_Install_Model::logError('File rollback error: ' . $this->escapeLogValue($targetFile));
            }

            if (str_ends_with(strtolower($targetFile), '.php') && function_exists('opcache_invalidate')) {
                @opcache_invalidate($targetFile, true);
            }
        }

        if ($restoredFiles || $restoreErrors) {
            Core_Install_Model::logInfo('Restored files total: ' . $restoredFiles);
            Core_Install_Model::logInfo('File rollback errors total: ' . $restoreErrors);
        }

        foreach (array_reverse($this->createdDirectories) as $directory) {
            @rmdir($directory);
        }

        $this->copiedFiles = [];
        $this->backups = [];
        $this->newFiles = [];
        $this->createdDirectories = [];
    }

    public function commitFiles(): void
    {
        $this->backups = [];
        $this->newFiles = [];
        $this->createdDirectories = [];
    }

    public function getCopiedFilesCount(): int
    {
        return count($this->copiedFiles);
    }

    public function getCopiedFiles(): array
    {
        return $this->copiedFiles;
    }

    protected function escapeLogValue(string $value): string
    {
        return htmlspecialchars(str_replace(DIRECTORY_SEPARATOR, '/', $value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
