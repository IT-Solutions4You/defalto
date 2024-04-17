<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Download_ZipArchive extends ZipArchive
{
    public function extractSubdirTo($destination, $subdir)
    {
        $errors = [];

        // Prepare dirs
        $destination = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $destination);
        $subdir = str_replace(["/", "\\"], "/", $subdir);

        if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, "UTF-8") * -1) != DIRECTORY_SEPARATOR) {
            $destination .= DIRECTORY_SEPARATOR;
        }

        if (substr($subdir, -1) != "/") {
            $subdir .= "/";
        }

        // Extract files
        for ($i = 0; $i < $this->numFiles; $i++) {
            $filename = $this->getNameIndex($i);

            if (substr($filename, 0, mb_strlen($subdir, "UTF-8")) == $subdir) {
                $relativePath = substr($filename, mb_strlen($subdir, "UTF-8"));
                $relativePath = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $relativePath);

                if (mb_strlen($relativePath, "UTF-8") > 0) {
                    if (substr($filename, -1) == "/")  // Directory
                    {
                        // New dir
                        if (!is_dir($destination . $relativePath)) {
                            if (!@mkdir($destination . $relativePath, 0755, true)) {
                                $errors[$i] = $filename;
                            }
                        }
                    } else {
                        if (dirname($relativePath) != ".") {
                            if (!is_dir($destination . dirname($relativePath))) {
                                // New dir (for file)
                                @mkdir($destination . dirname($relativePath), 0755, true);
                            }
                        }

                        // New file
                        if (@file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false) {
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
    public const ERROR = 'File not opened';
    public string $url = '';
    public string $dir = '/';
    public string $redirect = 'index.php';
    public string $progress = '';
    public int $progressMax = 5;
    public int $progressNum = 0;
    public string $version = '0.0.0';

    /**
     * @param string $url
     * @param string $dir
     * @param string $redirect
     *
     * @return Download
     * @throws Exception
     */
    public static function zip(string $version, string $dir = DIRECTORY_SEPARATOR, string $redirect = 'index.php'): Download
    {
        if (!session_id()) {
            session_start();
        }

        $self = new self();
        $self->url = sprintf('https://github.com/IT-Solutions4You/defalto/archive/refs/tags/%s.zip', $version);
        $self->dir = $dir;
        $self->redirect = $redirect;
        $self->version = $version;
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
        } else {
            $self->finish();
        }

        return $self;
    }

    public function retrieveProgress()
    {
        $this->progress = !empty($_SESSION['progress']) ? $_SESSION['progress'] : 'start';

        if ($this->is('start')) {
            $_SESSION['messages'] = [];
            $_SESSION['progress'] = '';
        }
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

    public function redirect()
    {
        unlink($this->getZipFileName());
        unlink($this->getPHPFileName());

        header('location:' . $this->redirect);
    }

    /**
     * @return string
     */
    public function getZipFileName(): string
    {
        return $this->getFileName() . '.zip';
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
     * @throws Exception
     */
    public function start()
    {
        self::log(self::START);
        $this->setProgress('retrieve', 1);
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public static function log(string $value)
    {
        $_SESSION['messages'][] = '[' . date('Y-m-d H:i:s') . ']: ' . print_r($value, true);
    }

    /**
     * @param string $value
     * @param int $number
     *
     * @return void
     */
    public function setProgress(string $value, int $number)
    {
        $this->progress = $_SESSION['progress'] = $value;
        $this->progressNum = $number * 20;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function retrieve()
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
     * @return string
     */
    public function getZipFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . $this->getZipFileName();
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

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @throws Exception
     */
    public function extract()
    {
        $zip = new Download_ZipArchive();
        $result = $zip->open($this->getZipFile());
        $versionFolder = 'defalto-' . $this->version . '/';

        if (true === $result) {
            self::log(self::FILE_OPENED);
            $zip->extractSubdirTo(__DIR__, $versionFolder);

            self::log(self::FILE_EXTRACTED);
            $zip->close();

            self::log(self::SUCCESS);
            $this->setProgress('finish', 4);
        } else {
            self::log(self::ERROR);
            $this->setProgress('error', 4);
        }
    }

    /**
     * @throws Exception
     */
    public function finish()
    {
        self::log(self::FINISH);
        $this->setProgress('', 5);
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
    public function getRedirectUrl(): string
    {
        return $this->getPHPFileName() . '?progress=redirect';
    }
}

$download = Download::zip('0.0.91');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta charset="utf-8">
    <style>
        body {
            background: #eee;
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
        }

        .progress {
            margin: 1em 0;
            width: 100%;
            border: 1px solid #ddd;
        }

        .progressBar {
            background: #eee;
            height: 1em;
        }

        .log {
            background: #fff;
            text-align: left;
            margin: 1em 0;
            padding: 0.5em;
            border: 1px solid #ddd;
        }

        .action {
            text-align: right;
            margin: 1em 0;
        }

        .button {
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
            padding: 0.8em 1em;
            background: #08c;
            color: #fff;
            border: 0;
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
            request.open('GET', 'install.php', true);
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
    <div class="progressContainer">
        <img class="logo" src="https://it-solutions4you.com/wp-content/uploads/2022/03/146x57_ITS4YOU_Logo.jpg" alt="Logo">
        <h1>Defalto <?php echo $download->version ?> installation progress</h1>
        <div class="progress">
            <div class="progressBar" style="width: <?php
            echo $download->progressNum ?>%;"></div>
        </div>
        <div class="log"><?php
            echo $download->getMessages() ?></div>
        <div class="action">
            <a href="<?php
            echo $download->getRedirectUrl() ?>" class="button hide">Continue Installation</a>
        </div>
    </div>
</div>
</body>
</html>