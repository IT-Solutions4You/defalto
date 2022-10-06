<?php

class ITS4YouDownload
{
    public const ZIP_CREATE = 'Zip file create';
    public const ZIP_CREATED = 'Zip file is created';
    public const ZIP_NOT_CREATED = 'Zip file is not created. Required to change write permissions to the root folder, files, sub folder and sub files';
    public const ZIP_WRITABLE = 'Zip file is writable';
    public const ZIP_COPIED = 'Zip file is copied';
    public const ZIP_NOT_COPIED = 'Zip file is not copied';
    public const ZIP_NOT_WRITABLE = 'Zip file is not writable';
    public const FILE_EXTRACTED = 'File extracted';
    public const FILE_OPENED = 'File opened';
    public const FINISH = 'Finish installation';
    public const START = 'Start installation';
    public const SUCCESS = 'File extract successfully';
    public const ERROR = 'File not opened';
    public $url = '';
    public $dir = '/';
    public $redirect = 'index.php';
    public $progress = '';
    public $progressMax = 5;
    public $progressNum = 0;

    /**
     * @param string $url
     * @param string $dir
     * @return ITS4YouDownload
     * @throws Exception
     */
    public static function zip($url, $dir = DIRECTORY_SEPARATOR, $redirect = 'index.php')
    {
        if (!session_id()) {
            session_start();
        }

        $self = new self();
        $self->url = $url;
        $self->dir = $dir;
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

    public function is($value)
    {
        return $this->progress === $value;
    }

    public function isRedirect()
    {
        return !empty($_REQUEST['progress']) && 'redirect' === $_REQUEST['progress'];
    }

    public function redirect()
    {
        unlink($this->getZipFileName());
        unlink($this->getPHPFileName());

        header('location:' . $this->redirect);
    }

    public function getZipFileName()
    {
        return $this->getFileName() . '.zip';
    }

    public function getFileName()
    {
        return basename(__FILE__, '.php');
    }

    public function getPHPFileName()
    {
        return $this->getFileName() . '.php';
    }

    public function start()
    {
        self::log(self::START);
        $this->setProgress('retrieve', 1);
    }

    /**
     * @throws Exception
     */
    public static function log($value)
    {
        $_SESSION['messages'][] = '[' . date('Y-m-d H:i:s') . ']: ' . print_r($value, true);
    }

    public function setProgress($value, $number)
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
    public function getZipFile()
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
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @throws Exception
     */
    public function extract()
    {
        $zip = new ZipArchive();
        $result = $zip->open($this->getZipFile());

        if (true === $result) {
            self::log(self::FILE_OPENED);
            $zip->extractTo(__DIR__);

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

    public function getMessages()
    {
        return implode('<br>', $_SESSION['messages']);
    }

    public function getRedirectUrl()
    {
        return $this->getPHPFileName() . '?progress=redirect';
    }
}

$download = ITS4YouDownload::zip('https://its4you.sk/en/images/extensions/ITS4YouCRM/ITS4YouCRM.zip');

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
        <h1>ITS4YouCRM installation progress</h1>
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
