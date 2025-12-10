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
        $this->success('Zip file extract process started');
        $rootDirectory = Core_Utils_Helper::getRootDirectory();
        $zip = new Installer_ZipArchive_Model();
        $result = $zip->open($this->getZipFile());

        if (true === $result) {
            $this->success('Zip file opened successfully');
            $zip->extractSubDirTo($rootDirectory, $this->folder);

            $this->success('Zip file extracted successfully');
            $zip->close();

            unlink($this->getZipFile());

            $this->success('Zip file removed successfully');
            $this->setProgress('update', 4);
        } else {
            $this->error('Zip file not opened');
            $this->setProgress('error', 4);
        }
    }

    /**
     * @throws Exception
     */
    public function finish(): void
    {
        $this->success('Finish extraction');
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
        $this->success('Start retrieve zip file process');

        if ($this->checkWritableFolders()) {
            $this->success('Permissions are set correctly');
        } else {
            $this->error('Set permissions to the root folder, files, sub folder and sub files');

            foreach (self::$writeableErrors as $error) {
                $this->error('Permissions not changed:' . $error);
            }

            $this->setProgress('error', 2);

            return;
        }

        $filename = $this->getZipFile();

        mkdir(dirname($filename), 0755, true);
        $this->success('Cache folder created successfully');

        file_put_contents($filename, '');
        $this->success('Zip file created successfully');

        if (is_file($filename)) {
            $this->success('Zip file is created');
            $this->setProgress('download', 2);
        } else {
            $this->error('Zip file is not created. Required to change write permissions to the root folder, files, sub folder and sub files');
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
        $this->success('Start download process');
        $this->setProgress('retrieve', 1);
    }

    public static array $writeableErrors = [];

    public function checkWritableFolders(): bool
    {
        $folders = [
            'modules',
            'layouts',
            'languages',
        ];

        foreach ($folders as $folder) {
            $folderDir = Core_Utils_Helper::getRootDirectory() . $folder;

            if (!is_writable($folderDir)) {
                self::$writeableErrors[] = $folder;
            } else {
                $this->success('Folder is writeable: ' . $folder);
            }
        }

        return empty(self::$writeableErrors);
    }

    /**
     * @return void
     */
    public function update(): void
    {
        $this->success('Composer updated process started');

        if (true === is_file('updateComposer.php')) {
            include_once 'updateComposer.php';

            $this->success('Composer updated successfully');
            $this->setProgress('finish', 5);
        } else {
            $this->error('Update composer error file not found');
            $this->setProgress('error', 5);
        }
    }

    public function success($message): void
    {
        Core_Install_Model::logSuccess($message);
    }

    public function error($message): void
    {
        Core_Install_Model::logError($message);
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
    public function download(): void
    {
        $this->success('Download zip file process started');
        $filename = $this->getZipFile();

        if (is_writable($filename)) {
            $this->success('Download zip file is writable');

            if (unlink($filename) && copy($this->getUrl(), $filename) && filesize($filename)) {
                $this->success('Download zip file copied successfully');
                $this->setProgress('extract', 3);
            } else {
                $this->error('Download zip file not copied.');
                $this->setProgress('error', 3);
            }
        } else {
            $this->error('Download zip file not writable.');
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

}