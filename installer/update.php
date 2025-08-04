<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Download_ZipArchive extends ZipArchive
{
    public static $skipFolders = [];
    public static $skipFiles = [];

    /**
     * @param string $destination
     * @param string $zipSubDir
     *
     * @return array
     */
    public function extractSubDirTo(string $destination, string $zipSubDir): array
    {
        $errors = [];

        // Prepare dirs
        $destination = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $destination);
        $zipSubDir = str_replace(["/", "\\"], "/", $zipSubDir);

        if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, 'UTF-8') * -1) != DIRECTORY_SEPARATOR) {
            $destination .= DIRECTORY_SEPARATOR;
        }

        if (!str_ends_with($zipSubDir, '/')) {
            $zipSubDir .= '/';
        }

        // Extract files
        for ($i = 0; $i < $this->numFiles; $i++) {
            $filename = $this->getNameIndex($i);

            if (substr($filename, 0, mb_strlen($zipSubDir, 'UTF-8')) == $zipSubDir) {
                $relativePath = substr($filename, mb_strlen($zipSubDir, 'UTF-8'));
                $relativePath = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $relativePath);

                if (mb_strlen($relativePath, 'UTF-8') > 0) {
                    if (str_ends_with($filename, '/'))  // Directory
                    {
                        // New dir
                        if (!is_dir($destination . $relativePath)) {
                            if (!mkdir($destination . $relativePath, 0755, true)) {
                                $errors[$i] = $filename;
                            }
                        }
                    } else {
                        if (dirname($relativePath) != '.') {
                            if (!is_dir($destination . dirname($relativePath))) {
                                // New dir (for file)
                                mkdir($destination . dirname($relativePath), 0755, true);
                            }
                        }

                        $skip = false;

                        foreach (self::$skipFolders as $skipFolder) {
                            if (str_starts_with($relativePath, $skipFolder . DIRECTORY_SEPARATOR)) {
                                $skip = true;
                            }
                        }

                        if (!$skip) {
                            foreach (self::$skipFiles as $skipFile) {
                                if (str_ends_with($filename, $skipFile)) {
                                    $skip = true;
                                }
                            }
                        }

                        if ($skip) {
                            Download::log('Skip: ' . $relativePath);
                        } elseif (file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false) {
                            $errors[$i] = $filename;
                        }
                    }
                }
            }
        }

        return $errors;
    }
}

class Download
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
        $zip = new Download_ZipArchive();
        $result = $zip->open($this->getZipFile());

        if (true === $result) {
            self::log(self::FILE_OPENED);
            $zip->extractSubDirTo(__DIR__, $this->folder);

            self::log(self::FILE_EXTRACTED);
            $zip->close();

            self::log(self::SUCCESS);
            $this->setProgress('update', 4);
        } else {
            self::log(self::ERROR);
            $this->setProgress('error', 4);
        }
    }

    /**
     * @throws Exception
     */
    public function finish(): void
    {
        self::log(self::FINISH);
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
        return __DIR__ . DIRECTORY_SEPARATOR . $this->getZipFileName();
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
     * @param string $value
     *
     * @return void
     */
    public static function log(string $value): void
    {
        $_SESSION['messages'][] = '[' . date('Y-m-d H:i:s') . ']: ' . print_r($value, true);
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

        self::log(self::ZIP_CREATE);

        file_put_contents($filename, '');

        if (is_file($filename)) {
            self::log(self::ZIP_CREATED);

            $this->setProgress('download', 2);
        } else {
            self::log(self::ZIP_NOT_CREATED);

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
        self::log(self::START);
        $this->setProgress('retrieve', 1);
    }

    /**
     * @return void
     */
    public function update()
    {
        if (true === is_file('updateComposer.php')) {
            include_once 'updateComposer.php';

            self::log(self::SUCCESS_COMPOSER);
            $this->setProgress('finish', 5);
        } else {
            self::log(self::ERROR);
            $this->setProgress('error', 5);
        }
    }

    /**
     * @param string $url
     * @param string $folder
     * @param string $redirect
     *
     * @return Download
     * @throws Exception
     */
    public static function zip(string $url, string $folder, string $redirect = 'index.php'): Download
    {
        if (!session_id()) {
            session_start();
        }

        $self = new self();
        $self->url = $url;
        $self->folder = $folder;
        $self->redirect = $redirect;
        $self->retrieveProgress();

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

    /**
     * @throws Exception
     */
    public function download()
    {
        $filename = $this->getZipFile();

        if (is_writable($filename)) {
            self::log(self::ZIP_WRITABLE);

            if (copy($this->getUrl(), $filename)) {
                self::log(self::ZIP_COPIED);
                $this->setProgress('extract', 3);
            } else {
                self::log(self::ZIP_NOT_COPIED);
                $this->setProgress('error', 3);
            }
        } else {
            self::log(self::ZIP_NOT_WRITABLE);
            $this->setProgress('error', 3);
        }
    }
}

$zipFileUrl = 'https://github.com/IT-Solutions4You/defalto/archive/refs/heads/develop.zip';
$zipFileFolder = 'defalto-develop';

Download_ZipArchive::$skipFolders = ['user_privileges', 'layouts/d1/modules/PDFMaker', 'modules/PDFMaker', 'manifest', 'update', 'icons', 'installer'];
Download_ZipArchive::$skipFiles = ['config.inc.php', 'composer.lock', 'index.php', 'update.php', 'install.php', 'parent_tabdata.php', 'tabdata.php', 'PDFMaker.php'];

$download = Download::zip($zipFileUrl, $zipFileFolder, 'index.php?module=Migration&view=Index&mode=step1');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta charset="utf-8">
    <style>
        body {
            padding: 0;
            margin: 0;
        }
        * {
            font-family: sans-serif;
        }

        .logo {
            margin: 1em 0;
        }

        .progressContainer {
            background: #fff;
            padding: 1em 2em;
            margin: 3em auto;
            width: 50vw;
            text-align: center;
            border-radius: 0.5rem;
        }

        .progressHeader {
            padding: 1em;
            text-align: center;
            color: #fff;
            background: #103962;
        }

        .progress {
            margin: 1em 0;
            width: 100%;
            border: 1px solid #ddd;
        }

        .progressBar {
            background: #103962;
            height: 1em;
        }

        .log {
            background: #fff;
            text-align: left;
            margin: 1em 0;
            padding: 0.5em;
            border: 1px solid #ddd;
            max-height: 50vh;
            overflow: auto;
        }

        .action {
            text-align: center;
            margin: 1em 0;
        }

        .button {
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            padding: 0.8em 1em;
            background: #103962;
            color: #fff;
            border: 0;
            border-radius: 0.5rem;
        }

        .hide {
            display: none;
        }
    </style>
    <script>
        function removeClass(selector, className) {
            const element = document.querySelector(selector);
            element.classList.remove(className);
        }

        function loadProgress() {
            const request = new XMLHttpRequest();

            request.onload = function () {
                let parser = new DOMParser(),
                    parserDocument = parser.parseFromString(this.responseText, 'text/html'),
                    parserContainer = parserDocument.querySelector('.replaceContainer'),
                    progress = parserContainer.attributes['data-progress']['value'],
                    replaceContainer = document.querySelector('.replaceContainer');

                replaceContainer.replaceWith(parserContainer);

                if ('' !== progress) {
                    setTimeout(function () {
                        loadProgress();
                    }, 1000);
                } else {
                    removeClass('.button', 'hide')
                }
            }
            request.open('GET', '<?php echo $download->getPHPFileName(); ?>', true);
            request.send();
        }

        setTimeout(function () {
            loadProgress();
        }, 1000);
    </script>
</head>
<body>
<div class="replaceContainer" data-progress="<?php
echo $download->progress ?>">
    <div class="progressHeader">
        <img class="logo" src="https://defalto.com/wp-content/uploads/2022/05/DefaltoCRMLogo170x40.png" alt="Logo">
        <h1>Defalto installation progress</h1>
    </div>
    <div class="progressContainer">
        <div class="progress">
            <div class="progressBar" style="width: <?php
            echo $download->progressNum ?>%;"></div>
        </div>
        <div class="log"><?php
            echo $download->getMessages() ?></div>
        <div class="action">
            <a href="<?php echo $download->getRedirectUrl() ?>" class="button hide">Continue Database Migration</a>
        </div>
    </div>
</div>
</body>
</html>