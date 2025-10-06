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
    public const ZIP_CREATE = 'Zip file create';
    public const ZIP_CREATED = 'Zip file is created';
    public const ZIP_NOT_CREATED = 'Zip file is not created. Required to change write permissions to the root folder, files, sub folder and sub files';
    public const ZIP_WRITABLE = 'Zip file is writable';
    public const ZIP_COPIED = 'Zip file is copied';
    public const ZIP_NOT_COPIED = 'Zip file is not copied';
    public const ZIP_NOT_WRITABLE = 'Zip file is not writable';
    public const FILE_EXTRACTED = 'File extracted';
    public const FILE_RENAME = 'Folder rename';
    public const FILE_OPENED = 'File opened';
    public const FINISH = 'Finish installation';
    public const START = 'Start installation';
    public const SUCCESS = 'File extract successfully';
    public const SUCCESS_COMPOSER = 'Composer updated successfully';
    public const ERROR = 'File not opened';
    public const ERROR_CHMOD = 'Set permissions to the root folder, files, sub folder and sub files';
    /**
     * @var string
     */
    public string $dir = '/';
    /**
     * @var string
     */
    public string $folder = '0.0.0';
    /**
     * @var string
     */
    public string $progress = '';
    /**
     * @var int
     */
    public int $progressMax = 5;
    /**
     * @var int
     */
    public int $progressNum = 0;
    /**
     * @var string
     */
    public string $redirect = 'index.php';
    /**
     * @var string
     */
    public string $url = '';

    /**
     * @throws Exception
     */
    public function extract(): void
    {
        global $root_directory;

        $zip = new Installer_ZipArchive_Model();
        $result = $zip->open($this->getZipFile());

        if (true === $result) {
            Core_Install_Model::logSuccess(self::FILE_OPENED);
            $zip->extractSubDirTo($root_directory, $this->folder);

            Core_Install_Model::logSuccess(self::FILE_EXTRACTED);
            $zip->close();

            unlink($this->getZipFile());

            Core_Install_Model::logSuccess(self::SUCCESS);
            $this->setProgress('update', 4);
        } else {
            Core_Install_Model::logError(self::ERROR);
            $this->setProgress('error', 4);
        }
    }

    /**
     * @throws Exception
     */
    public function finish(): void
    {
        Core_Install_Model::logSuccess(self::FINISH);
        $this->setProgress('', 6);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return basename(__FILE__, '.php');
    }

    /**
     * @return string
     */
    public function getMessages(): string
    {
        return implode('<br>', $_SESSION['messages']);
    }

    /**
     * @return string
     */
    public function getPHPFileName(): string
    {
        return $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->getPHPFileName() . '?progress=redirect';
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getZipFile(): string
    {
        global $root_directory;

        return rtrim($root_directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->getZipFileName();
    }

    /**
     * @return string
     */
    public function getZipFileName(): string
    {
        return $this->getFileName() . '.zip';
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function is(string $value): bool
    {
        return $this->progress === $value;
    }

    /**
     * @return bool
     */
    public function isRedirect(): bool
    {
        return !empty($_REQUEST['progress']) && 'redirect' === $_REQUEST['progress'];
    }

    /**
     * @return void
     */
    public function redirect(): void
    {
        unlink($this->getZipFileName());
        unlink($this->getPHPFileName());

        header('location:' . $this->redirect);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function retrieve(): void
    {
        $filename = $this->getZipFile();

        Core_Install_Model::logSuccess(self::ZIP_CREATE);

        mkdir(dirname($filename), 0755, true);
        file_put_contents($filename, '');

        if (is_file($filename)) {
            Core_Install_Model::logSuccess(self::ZIP_CREATED);
            $this->setProgress('download', 2);
        } else {
            Core_Install_Model::logError(self::ZIP_NOT_CREATED);
            $this->setProgress('error', 2);
        }
    }

    /**
     * @return void
     */
    public function retrieveProgress(): void
    {
        $this->progress = !empty($_SESSION['progress']) ? $_SESSION['progress'] : 'start';

        if ($this->is('start')) {
            $_SESSION['messages'] = [];
            $_SESSION['progress'] = '';
        }
    }

    /**
     * @param string $value
     * @param int    $number
     *
     * @return void
     */
    public function setProgress(string $value, int $number): void
    {
        $this->progress = $_SESSION['progress'] = $value;
        $this->progressNum = min(100, $number * 17);
    }

    /**
     * @throws Exception
     */
    public function start(): void
    {
        global $root_directory;

        if (self::chmod($root_directory)) {
            Core_Install_Model::logSuccess(self::START);
            $this->setProgress('retrieve', 1);
        } else {
            Core_Install_Model::logError(self::ERROR_CHMOD);
            Core_Install_Model::logError('Permissions not changed:' . implode(',', self::$chmodErrors));
        }
    }

    /**
     * @return void
     */
    public function update(): void
    {
        if (true === is_file('updateComposer.php')) {
            include_once 'updateComposer.php';

            Core_Install_Model::logSuccess(self::SUCCESS_COMPOSER);
            $this->setProgress('finish', 5);
        } else {
            Core_Install_Model::logError(self::ERROR);
            $this->setProgress('error', 5);
        }
    }

    /**
     * @param string $url
     * @param string $folder
     * @param string $redirect
     *
     * @return self
     * @throws Exception
     */
    public static function zip(string $url, string $folder, string $redirect = 'index.php'): self
    {
        if (!session_id()) {
            session_start();
        }

        $self = self::getInstance($url, $folder, $redirect);

        if ($self->isRedirect()) {
            $self->redirect();
        } elseif ($self->is('start')) {
            $self->start();
        } elseif ($self->is('retrieve')) {
            $self->retrieve();
        } elseif ($self->is('download')) {
            $self->download();
        } elseif ($self->is('extract')) {
            $self->extract();
        } elseif ($self->is('update')) {
            $self->update();
        } else {
            $self->finish();
        }

        return $self;
    }

    public static function getInstance(string $url, string $folder, string $redirect = 'index.php'): self
    {
        $self = new self();
        $self->url = $url;
        $self->folder = $folder;
        $self->redirect = $redirect;
        $self->retrieveProgress();

        return $self;
    }

    /**
     * @throws Exception
     */
    public function download()
    {
        $filename = $this->getZipFile();

        if (is_writable($filename)) {
            Core_Install_Model::logSuccess(self::ZIP_WRITABLE);

            if (copy($this->getUrl(), $filename)) {
                Core_Install_Model::logSuccess(self::ZIP_COPIED);
                $this->setProgress('extract', 3);
            } else {
                Core_Install_Model::logError(self::ZIP_NOT_COPIED);
                $this->setProgress('error', 3);
            }
        } else {
            Core_Install_Model::logError(self::ZIP_NOT_WRITABLE);
            $this->setProgress('error', 3);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function downloadAndExport(): void
    {
        if ($this->isRedirect()) {
            $this->redirect();
        }

        if ($this->is('start')) {
            $this->start();
        }

        if ($this->is('retrieve')) {
            $this->retrieve();
        }

        if ($this->is('download')) {
            $this->download();
        }

        if ($this->is('extract')) {
            $this->extract();
        }

        if ($this->is('update')) {
            $this->update();
        }

        $this->finish();
    }

    public static array $chmodErrors = [];

    /**
     * @param string $path
     * @return true
     */
    public static function chmod(string $path): bool
    {
        $folderPerm = 0777;
        $filePerm = 0777;
        $dp = opendir($path);

        if(!chmod($path, $folderPerm)) {
            self::$chmodErrors[] = $path;
        }

        while ($file = readdir($dp)) {
            if ($file != '.' and $file != '..') {
                $file = $path . '/' . $file;

                if (is_dir($file)) {
                    self::chmod($file);
                } elseif (!chmod($file, $filePerm)) {
                    self::$chmodErrors[] = $file;
                }
            }
        }

        closedir($dp);

        return empty(self::$chmodErrors);
    }
}