<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'include/Migration/MigrationUtils.php';

class DatabaseMigrations
{
    protected PearDatabase $db;
    protected string $command;
    protected string $commandType;
    protected string $migrationsTableName = 'its4you_migrations';
    protected string $migrationPath = 'schema/its4youmigrations/';
    protected array $migrationCommands = ['list', 'migrate', 'create', 'help'];
    protected string $helpMsg = '
            Specify command of migration! 
            e.g.: php cron/its4youmigration.php list #.............................. to view prepared migration list
            e.g.: php cron/its4youmigration.php migrate #........................... to execute migrations with confirmation
            e.g.: php cron/its4youmigration.php migrate -y #........................ to execute migrations without confirmation
            e.g.: php cron/its4youmigration.php migrate --y #....................... to execute migrations without confirmation
            e.g.: php cron/its4youmigration.php create #..... to create migration file in /base migrations folder
            e.g.: php cron/its4youmigration.php create -c #.. to create migration file in /customer migrations folder
            e.g.: php cron/its4youmigration.php create --c #. to create migration file in /customer migrations folder
            to display help see: php cron/its4youmigration.php help';

    /**
     * @param array $arg - Array of arguments passed to script
     */
    public function __construct($arg)
    {
        require_once('include/utils/utils.php');
        require_once('include/logging.php');
        require_once('include/Migration/AbstractMigrations.php');

        require_once('modules/com_vtiger_workflow/include.inc');
        require_once('modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc');
        require_once('modules/com_vtiger_workflow/VTEntityMethodManager.inc');

        $this->db = PearDatabase::getInstance();

        if (is_countable($arg) && count($arg) > 0) {
            try {
                $this->parseArg($arg);
                $this->handleMigration();
            } catch (Exception $objEx) {
                $this->makeAborting('Migration does not work!');
            }
        } else {
            $this->makeAborting($this->helpMsg);
        }
    }

    /**
     * @return void
     */
    private function handleMigration(): void
    {
        switch ($this->command) {
            case 'list':
                $this->listUpdates();
                break;
            case 'migrate':
                $this->run();
                $this->loadClasses();
                break;
            case 'create':
                $folderName = $this->migrationPath . date('Y') . DIRECTORY_SEPARATOR . date('m');

                if (!is_dir($folderName)) {
                    if (!mkdir($folderName, 0755, true) && !is_dir($folderName)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $folderName));
                    }
                }

                $migrationName = date('YmdHis');
                $fileName = $migrationName . '.php';
                $fullFileName = $folderName . '/' . $fileName;

                if (!file_exists($fullFileName)) {
                    file_put_contents($fullFileName, $this->getTemplateContent($migrationName));
                    $this->showMsg('created: ' . $migrationName);
                } else {
                    $this->makeAborting('Migration file ' . $fullFileName . ' already exist!');
                }

                break;
            case 'help':
                $this->makeAborting($this->helpMsg);
                break;
        }
    }

    /**
     * Returns template of migration file
     *
     * @param string $migrationName
     *
     * @return string
     */
    private function getTemplateContent(string $migrationName): string
    {
        return "<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('ITS4YouMigration_$migrationName')) {
    class ITS4YouMigration_$migrationName extends ITS4YouMigrationParent
    {
        /**
         * @param string $" . "strFileName
         */
        public function migrate(string $" . "strFileName): void
        {
             
        }
    }
} else {
    $" . "baseFileName = str_replace('.php', '', basename(__FILE__));
    $" . "this->makeAborting($" . "this->wrongClassName . $" . "baseFileName);
}";
    }

    /**
     * @param array $arg
     *
     * @return void
     */
    protected function parseArg(array $arg): void
    {
        if (isset($arg[1]) && !empty($arg[1])) {
            if (in_array($arg[1], $this->migrationCommands)) {
                $this->command = $arg[1];

                if (('create' === $arg[1]) && in_array($arg[3], ['-c', '--c'])) {
                    $this->migrationPath .= 'customer/';
                }
            } else {
                $this->makeAborting('Command handler not found!');
            }
        } else {
            $this->makeAborting('Specify command of migration');
        }
    }

    /**
     * Display list of migrations ready to run
     *
     * @return void
     */
    public function listUpdates(): void
    {
        $filesToRun = $this->loadMigrationFiles();
        $done = 1;

        if ((is_countable($filesToRun) ? count($filesToRun) : 0) > 0) {
            foreach ($filesToRun as $strFileName) {
                $this->showMsg(" $strFileName ... [" . $done . "/" . (is_countable($filesToRun) ? count($filesToRun) : 0) . "]");
                $done++;
            }
        } else {
            $this->showMsg('No files available to migrate');
        }
    }

    /**
     * Check migration table and run migration scripts
     *
     * @return void
     */
    public function run(): void
    {
        $this->checkMigrationTable();

        $this->runMigrations();
    }

    /**
     * Include files from vtlib
     *
     * @return void
     */
    protected function loadClasses(): void
    {
        $vtlib = glob('vtlib/Vtiger/*.php');

        if (!empty($vtlib)) {
            foreach ($vtlib as $filePath) {
                if (file_exists($filePath)) {
                    require_once $filePath;
                }
            }
        }
    }

    /**
     * Load migration Files
     *
     * @return array of migration files which have to run in migration
     */
    protected function loadMigrationFiles(): array
    {
        $allMigrationFiles = $this->loadAllMigrationFiles();
        $filesToRun = [];

        if (!empty($allMigrationFiles)) {
            $allRunnedFiles = $this->loadRunFiles();

            foreach ($allMigrationFiles as $fileName) {
                if (!in_array($fileName, $allRunnedFiles)) {
                    $filesToRun[] = $fileName;
                }
            }
        }

        return $filesToRun;
    }

    /**
     * Load all migration Files recursive
     * Return Array of all migration file Names
     *
     * @param string $pattern
     * @param int    $flags
     *
     * @return array|false
     */
    protected function glob_recursive(string $pattern, int $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            if (preg_match('/.php/', basename($pattern))) {
                $files = array_merge($files, $this->glob_recursive($dir . '/' . basename($pattern), $flags));
            }
        }

        return $files;
    }

    /**
     * Load all migration Files
     * Return Array of all migration file Names
     *
     * @return array
     */
    protected function loadAllMigrationFiles(): array
    {
        $allMigrationFilesArray = [];

        if (file_exists($this->migrationPath)) {
            $allMigrationFiles = $this->glob_recursive($this->migrationPath . '*.php');

            if (!empty($allMigrationFiles)) {
                foreach ($allMigrationFiles as $filePath) {
                    if (preg_match('/.php/', basename($filePath))) {
                        require_once($filePath);

                        if (class_exists('ITS4YouMigration_' . substr(basename($filePath), 0, -4))) {
                            $allMigrationFilesArray[] = $filePath;
                        }
                    }
                }
            }
        } else {
            $this->showMsg('Path ' . $this->migrationPath . ' does not exist!');
        }

        asort($allMigrationFilesArray);

        return $allMigrationFilesArray;
    }

    /**
     * Migration files which were run by migration script already
     *
     * @return array
     */
    protected function loadRunFiles(): array
    {
        $result = $this->db->pquery('SELECT migration_name FROM ' . $this->migrationsTableName . ' WHERE migration_status = ?', [1]);
        $num_rows = $this->db->num_rows($result);
        $allRunFiles = [];

        if ($num_rows > 0) {
            while ($migrationRow = $this->db->fetchByAssoc($result)) {
                $allRunFiles[] = $migrationRow['migration_name'];
            }
        }

        return $allRunFiles;
    }

    /**
     * method to check if migration table exists
     *
     * @return void
     */
    protected function checkMigrationTable(): void
    {
        $this->db->pquery(
            'CREATE TABLE IF NOT EXISTS ' . $this->migrationsTableName . ' (
                                `migration_name` varchar(255) NOT NULL,
                                `migration_createdtime` datetime NOT NULL,
                                `migration_status` int(11) DEFAULT "0" COMMENT "0-created,1-finished,2-inprogress",
                              PRIMARY KEY (`migration_name`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            []
        );
    }

    /**
     * Run migrations
     *
     * @return void
     */
    protected function runMigrations(): void
    {
        $filesToRun = $this->loadMigrationFiles();

        if ((is_countable($filesToRun) ? count($filesToRun) : 0) > 0) {
            //for command '--y' and '-y'
            $optCommand = $this->commandType;

            if (!in_array($optCommand, ['-y', '--y'])) {
                $this->showMsg((is_countable($filesToRun) ? count($filesToRun) : 0) . ' migration-file(s) will be upgraded');

                $this->showMsg('Do you want to RUN these files?' . "\n" . 'Type "yes" to continue: ');
                $line = fgets(STDIN);

                if (trim($line) != 'yes') {
                    $this->makeAborting();
                }

                $this->showMsg('continuing...');
            }

            $done = 1;
            $this->db->setDieOnError(true);

            foreach ($filesToRun as $strFileName) {
                $this->showMsg("Start - $strFileName ... [" . $done . "/" . (is_countable($filesToRun) ? count($filesToRun) : 0) . "]");
                $this->markMigration($strFileName);
                $this->migrate($strFileName);
                $this->showMsg("Done - $strFileName ... [" . $done . "/" . (is_countable($filesToRun) ? count($filesToRun) : 0) . "]");
                $done++;
            }

            $this->db->setDieOnError(false);

            $done_display = $done - 1;
            $this->showMsg("\nMigrations done:  $done_display  file(s)\n");
        } else {
            $this->showMsg('No files available to migrate');
        }
    }

    /**
     * Run migration
     *
     * @param string $strFileName
     *
     * @return void
     */
    protected function migrate(string $strFileName): void
    {
        $fullFilePath = $strFileName;

        if (file_exists($fullFilePath)) {
            $migrationClassName = 'ITS4YouMigration_' . str_replace('.php', '', basename($strFileName));
            require_once $fullFilePath;

            if (class_exists($migrationClassName)) {
                $migrationObj = new $migrationClassName();

                if (is_subclass_of($migrationObj, 'AbstractMigrations')) {
                    /** / migration created - mark as 2-inprogress / */
                    $this->markMigration($strFileName, 2);
                    /** / Start transaction / */
                    $this->db->pquery('START TRANSACTION;', []);
                    if (method_exists($migrationObj, 'migrate')) {
                        $migrationObj->migrate($strFileName);
                    } else {
                        $this->showMsg("The class $migrationClassName has got no 'migrate' method! ");
                    }
                    /** / commit all db changes in migration / */
                    $this->db->pquery('COMMIT;', []);
                    /** / migration created - mark as 1-finished / */
                    $this->markMigration($strFileName, 1);
                } else {
                    $this->showMsg("The class $migrationClassName is not extended from ITS4YouMigrationParent! ");
                }
            } else {
                $this->showMsg("The class-name for $strFileName is not correct! ");
            }
        } else {
            $this->showMsg("The migration-file $strFileName does not exist! ");
        }
    }

    /**
     * Set status for migration file
     *
     * Recognized values:
     * 0 - created
     * 1 - finished
     * 2 - inprogress
     *
     * @param string $fileName
     * @param int    $migration_status
     *
     * @return void
     */
    protected function markMigration(string $fileName, int $migration_status = 0): void
    {
        if ($fileName != '') {
            $this->db->pquery('REPLACE INTO its4you_migrations (migration_name,migration_createdtime,migration_status) VALUES (?,now(),?)', [$fileName, $migration_status]);
        }
    }

    /**
     * @param $message
     *
     * @return void
     */
    protected function makeAborting($message = null): void
    {
        MigrationUtils::makeAborting($message);
    }

    /**
     * @param $message
     *
     * @return void
     */
    protected function showMsg($message = null): void
    {
        MigrationUtils::showMsg($message);
    }
}