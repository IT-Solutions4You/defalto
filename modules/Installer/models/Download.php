<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
class Installer_Download_Model
{
    public const CONNECT_TIMEOUT = 15;
    public const REQUEST_TIMEOUT = 90;
    public const MAX_DOWNLOAD_SIZE = 209715200;

    public string $dir = '/';
    public string $folder = '0.0.0';
    public string $progress = '';
    public int $progressMax = 5;
    public int $progressNum = 0;
    public string $redirect = 'index.php';
    public string $url = '';
    public static array $writeableErrors = [];

    protected string $packageName = 'Download';
    protected string $expectedChecksum = '';
    protected string $workspace = '';
    protected array $allowedRoots = [];
    protected array $requiredWritablePaths = ['cache', 'modules', 'layouts', 'languages'];
    protected mixed $lockHandle = null;
    protected ?Installer_ZipArchive_Model $archive = null;
    protected bool $finalized = false;
    protected bool $composerUpdateAttempted = false;

    public function __destruct()
    {
        if (!$this->finalized && ($this->workspace !== '' || is_resource($this->lockHandle) || $this->archive)) {
            $this->rollback();
        }
    }

    /** @throws Exception */
    public function start(): void
    {
        $this->success('Start download process');
        $this->setProgress('retrieve', 1);
    }

    /** @throws Exception */
    public function retrieve(): void
    {
        $this->success('Start retrieve zip file process');
        $this->initializeWorkspace();
        $this->acquireLock();

        if (!$this->checkWritableFolders()) {
            foreach (self::$writeableErrors as $error) {
                $this->error('Path is not writable: ' . $this->escapeLogValue($error));
            }

            throw new RuntimeException('Installer cannot write required paths: ' . implode(', ', self::$writeableErrors));
        }

        $this->success('Required paths are writable');
        $this->setProgress('download', 2);
    }

    /** @throws Exception */
    public function download(): void
    {
        $this->success('Download zip file process started');
        $this->validateDownloadUrl($this->getUrl());
        $filename = $this->getZipFile();
        $directory = dirname($filename);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create installer workspace');
        }

        $temporaryFile = $filename . '.part';
        $source = $this->openDownloadStream($this->getUrl());
        $target = @fopen($temporaryFile, 'wb');

        if (!is_resource($target)) {
            fclose($source);
            throw new RuntimeException('Unable to create the temporary ZIP file');
        }

        $size = 0;
        $startedAt = microtime(true);
        $downloadError = null;

        try {
            while (!feof($source)) {
                if (microtime(true) - $startedAt > self::REQUEST_TIMEOUT) {
                    throw new RuntimeException('ZIP download exceeded the request timeout');
                }

                $chunk = fread($source, 1048576);

                if (false === $chunk) {
                    throw new RuntimeException('Unable to read the downloaded ZIP stream');
                }

                if (!empty(stream_get_meta_data($source)['timed_out'])) {
                    throw new RuntimeException('ZIP download timed out');
                }

                $size += strlen($chunk);

                if ($size > self::MAX_DOWNLOAD_SIZE) {
                    throw new RuntimeException('Downloaded ZIP exceeds the maximum allowed size');
                }

                if ($chunk !== '' && strlen($chunk) !== fwrite($target, $chunk)) {
                    throw new RuntimeException('Unable to write the downloaded ZIP');
                }
            }
        } catch (Throwable $throwable) {
            $downloadError = $throwable;
        } finally {
            fclose($source);
            fclose($target);
        }

        if ($downloadError) {
            @unlink($temporaryFile);

            throw $downloadError;
        }

        if ($size < 4 || !$this->hasZipSignature($temporaryFile)) {
            @unlink($temporaryFile);
            throw new RuntimeException('Downloaded response is not a valid ZIP file');
        }

        $checksum = hash_file('sha256', $temporaryFile);

        if (!is_string($checksum)) {
            @unlink($temporaryFile);
            throw new RuntimeException('Unable to calculate downloaded ZIP checksum');
        }

        if ($this->expectedChecksum !== '' && !hash_equals($this->expectedChecksum, $checksum)) {
            @unlink($temporaryFile);
            throw new RuntimeException('Downloaded ZIP checksum does not match the expected SHA-256');
        }

        if (is_file($filename) && !unlink($filename)) {
            @unlink($temporaryFile);
            throw new RuntimeException('Unable to replace the previous ZIP file');
        }

        if (!rename($temporaryFile, $filename)) {
            @unlink($temporaryFile);
            throw new RuntimeException('Unable to finalize the downloaded ZIP file');
        }

        $this->success('Download zip file copied successfully');
        $this->success('Downloaded ZIP: ' . $this->escapeLogValue($this->getLogZipPath()));
        $this->success('Downloaded ZIP size: ' . $size . ' bytes');
        $this->success('Downloaded ZIP SHA-256: ' . $checksum);
        $this->setProgress('extract', 3);
    }

    /** @throws Exception */
    public function extract(): void
    {
        $this->success('Zip file extract process started');
        $this->archive = new Installer_ZipArchive_Model();
        $result = $this->archive->open($this->getZipFile());

        if (true !== $result) {
            throw new RuntimeException('Downloaded ZIP file cannot be opened (code ' . (string)$result . ')');
        }

        try {
            $this->success('Zip file opened successfully');
            $this->success('Expected ZIP source folder: ' . $this->escapeLogValue($this->folder));
            $this->archive->setAllowedRoots($this->allowedRoots);
            $sourceFolder = $this->archive->resolveSourceFolder($this->folder);

            if ($sourceFolder !== trim(str_replace('\\', '/', $this->folder), '/')) {
                $this->success('Detected ZIP source folder: ' . $this->escapeLogValue($sourceFolder));
                $this->success('Using detected ZIP source folder because the API folder did not match the package');
            }

            $errors = $this->archive->extractSubDirTo(
                Core_Utils_Helper::getRootDirectory(),
                $sourceFolder,
                $this->getWorkspace()
            );

            if ($errors || $this->archive->getCopiedFilesCount() < 1) {
                throw new RuntimeException(
                    $errors ? 'ZIP extraction failed: ' . implode(', ', $errors) : 'ZIP package did not contain installable files'
                );
            }
        } finally {
            $this->archive->close();
        }

        $this->success('Zip file extracted successfully');
        $this->setProgress('update', 4);
    }

    /** @throws Exception */
    public function update(): void
    {
        $this->success('Composer updated process started');
        $this->composerUpdateAttempted = true;
        $result = $this->runComposerUpdate();

        if (0 !== $result['exit_code']) {
            throw new RuntimeException('Composer update failed with exit code ' . $result['exit_code']);
        }

        $this->success('Composer updated successfully');
        $this->setProgress('finish', 5);
    }

    public function finish(): void
    {
        $this->success('Finish extraction');
        $this->setProgress('', 6);
    }

    /**
     * The caller must commit after the database installation succeeds, or roll
     * the copied files back when a later installation step fails.
     *
     * @throws Throwable
     */
    public function downloadAndExport(bool $runComposerUpdate = false): void
    {
        try {
            $this->start();
            $this->retrieve();
            $this->download();

            if ($runComposerUpdate) {
                $this->prepareComposerRunner();
            }

            $this->extract();

            if ($runComposerUpdate) {
                $this->update();
            }

            $this->finish();
        } catch (Throwable $throwable) {
            $this->error('Package installation failed: ' . $this->escapeLogValue($throwable->getMessage()));
            $this->rollback();

            throw $throwable;
        }
    }

    public function commit(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->archive?->commitFiles();
        $this->removeWorkspace();
        $this->releaseLock();
        $this->finalized = true;
        $this->success('Package files committed');
    }

    public function rollback(): void
    {
        if ($this->finalized) {
            return;
        }

        $this->archive?->rollbackFiles();

        if ($this->composerUpdateAttempted) {
            try {
                $result = $this->runComposerUpdate();

                if (0 === $result['exit_code']) {
                    $this->success('Composer dependencies restored after rollback');
                } else {
                    $this->error('Composer rollback failed with exit code ' . $result['exit_code']);
                }
            } catch (Throwable $throwable) {
                $this->error('Composer rollback failed: ' . $this->escapeLogValue($throwable->getMessage()));
            }
        }

        $this->removeWorkspace();
        $this->releaseLock();
        $this->finalized = true;
        $this->error('Package file changes rolled back');
    }

    public function checkWritableFolders(): bool
    {
        self::$writeableErrors = [];
        $rootDirectory = rtrim(Core_Utils_Helper::getRootDirectory(), '/\\') . DIRECTORY_SEPARATOR;

        foreach (array_unique($this->requiredWritablePaths) as $path) {
            $fullPath = $rootDirectory . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
            $checkPath = file_exists($fullPath) ? $fullPath : dirname($fullPath);

            while (!file_exists($checkPath) && dirname($checkPath) !== $checkPath) {
                $checkPath = dirname($checkPath);
            }

            if (!is_writable($checkPath)) {
                self::$writeableErrors[] = $path;
            } else {
                $this->success('Path is writable: ' . $this->escapeLogValue($path));
            }
        }

        return !self::$writeableErrors;
    }

    public function setAllowedRoots(array $roots): static
    {
        $this->allowedRoots = array_values(array_unique(array_filter(array_map('strval', $roots))));

        return $this;
    }

    public function setRequiredWritablePaths(array $paths): static
    {
        $this->requiredWritablePaths = array_values(array_unique(array_filter(array_map('strval', $paths))));

        return $this;
    }

    public function setExpectedChecksum(string $checksum): static
    {
        $checksum = strtolower(trim($checksum));

        if ($checksum !== '' && !preg_match('/^[a-f0-9]{64}$/', $checksum)) {
            throw new InvalidArgumentException('Expected ZIP checksum must be a SHA-256 value');
        }

        $this->expectedChecksum = $checksum;

        return $this;
    }

    protected function validateDownloadUrl(string $url): void
    {
        $parts = parse_url($url);

        if (!$parts || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            throw new InvalidArgumentException('Package download URL must use HTTP or HTTPS');
        }
    }

    /** @return resource */
    protected function openDownloadStream(string $url): mixed
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::CONNECT_TIMEOUT,
                'follow_location' => 1,
                'max_redirects' => 5,
                'ignore_errors' => true,
                'user_agent' => 'Defalto Installer',
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $stream = @fopen($url, 'rb', false, $context);

        if (!is_resource($stream)) {
            throw new RuntimeException('Unable to connect to the package server');
        }

        stream_set_timeout($stream, self::REQUEST_TIMEOUT);
        $metadata = stream_get_meta_data($stream);
        $headers = (array)($metadata['wrapper_data'] ?? []);
        $statusCode = null;

        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/', (string)$header, $matches)) {
                $statusCode = (int)$matches[1];
            }
        }

        if (null !== $statusCode && ($statusCode < 200 || $statusCode >= 300)) {
            fclose($stream);
            throw new RuntimeException('Package server returned HTTP ' . $statusCode);
        }

        return $stream;
    }

    protected function hasZipSignature(string $filename): bool
    {
        $handle = @fopen($filename, 'rb');

        if (!is_resource($handle)) {
            return false;
        }

        $signature = fread($handle, 4);
        fclose($handle);

        return in_array($signature, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true);
    }

    protected function initializeWorkspace(): void
    {
        if ($this->workspace !== '') {
            return;
        }

        $cacheDirectory = rtrim(Core_Utils_Helper::getRootDirectory(), '/\\') . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'installer';
        $this->workspace = $cacheDirectory . DIRECTORY_SEPARATOR . date('YmdHis') . '-'
            . preg_replace('/[^A-Za-z0-9_.-]/', '_', $this->packageName) . '-' . bin2hex(random_bytes(6));

        if (!is_dir($this->workspace) && !mkdir($this->workspace, 0755, true) && !is_dir($this->workspace)) {
            throw new RuntimeException('Unable to create isolated installer workspace');
        }
    }

    protected function acquireLock(): void
    {
        if (is_resource($this->lockHandle)) {
            return;
        }

        $lockDirectory = rtrim(Core_Utils_Helper::getRootDirectory(), '/\\') . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . 'locks';

        if (!is_dir($lockDirectory) && !mkdir($lockDirectory, 0755, true) && !is_dir($lockDirectory)) {
            throw new RuntimeException('Unable to create installer lock directory');
        }

        $lockFile = $lockDirectory . DIRECTORY_SEPARATOR . 'installer.lock';
        $this->lockHandle = @fopen($lockFile, 'c');

        if (!is_resource($this->lockHandle) || !flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            $this->releaseLock();
            throw new RuntimeException('Another Installer package operation is already running');
        }
    }

    protected function releaseLock(): void
    {
        if (is_resource($this->lockHandle)) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
        }

        $this->lockHandle = null;
    }

    protected function removeWorkspace(): void
    {
        if ($this->workspace === '' || !is_dir($this->workspace)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->workspace, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($this->workspace);
    }

    protected function prepareComposerRunner(): void
    {
        if (!class_exists(Installer_Composer_Model::class)) {
            throw new RuntimeException('Unable to initialize the Installer Composer runner');
        }
    }

    protected function runComposerUpdate(): array
    {
        $result = Installer_Composer_Model::installDependencies(
            rtrim(Core_Utils_Helper::getRootDirectory(), '/\\')
        );

        if (!is_array($result) || !isset($result['exit_code'])) {
            throw new RuntimeException('Composer update runner returned an invalid result');
        }

        return [
            'exit_code' => (int)$result['exit_code'],
            'output' => (string)($result['output'] ?? ''),
        ];
    }

    protected function escapeLogValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function getLogZipPath(): string
    {
        $rootDirectory = str_replace('\\', '/', rtrim(Core_Utils_Helper::getRootDirectory(), '/\\')) . '/';
        $zipFile = str_replace('\\', '/', $this->getZipFile());

        return str_starts_with($zipFile, $rootDirectory) ? substr($zipFile, strlen($rootDirectory)) : basename($zipFile);
    }

    public function getWorkspace(): string
    {
        $this->initializeWorkspace();

        return $this->workspace;
    }

    public function getFileName(): string
    {
        return basename(__FILE__, '.php');
    }

    public function getPHPFileName(): string
    {
        return $this->getFileName() . '.php';
    }

    public function getRedirectUrl(): string
    {
        return $this->getPHPFileName() . '?progress=redirect';
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getZipFile(): string
    {
        return $this->getWorkspace() . DIRECTORY_SEPARATOR . 'package.zip';
    }

    public function getZipFileName(): string
    {
        return 'package.zip';
    }

    public function is(string $value): bool
    {
        return $this->progress === $value;
    }

    public function isRedirect(): bool
    {
        return !empty($_REQUEST['progress']) && 'redirect' === $_REQUEST['progress'];
    }

    public function redirect(): void
    {
        header('location:' . $this->redirect);
    }

    public function retrieveProgress(): void
    {
        $this->progress = 'start';
    }

    public function setProgress(string $value, int $number): void
    {
        $this->progress = $value;
        $this->progressNum = min(100, $number * 17);
    }

    public function success(mixed $message): void
    {
        Core_Install_Model::logSuccess($message);
    }

    public function error(mixed $message): void
    {
        Core_Install_Model::logError($message);
    }

    public static function zip(string $url, string $folder, string $redirect = 'index.php'): self
    {
        $self = self::getInstance($url, $folder, $redirect);
        $self->downloadAndExport(true);
        $self->commit();

        return $self;
    }

    public static function getInstance(
        string $url,
        string $folder,
        string $redirect = 'index.php',
        string $packageName = 'Download'
    ): self {
        $self = new self();
        $self->url = $url;
        $self->folder = $folder;
        $self->redirect = $redirect;
        $self->packageName = $packageName;
        $self->retrieveProgress();

        return $self;
    }
}
