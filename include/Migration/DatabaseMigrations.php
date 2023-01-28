<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'include/Migration/MigrationsTrait.php';
require_once 'include/Migration/MigrationsDatabaseWrapper.php';

class DatabaseMigrations
{
    use MigrationsTrait;

    protected PearDatabase $db;
    protected MigrationsDatabaseWrapper $migrationsDatabaseWrapper;
    protected string $command;
    protected string $commandType;
    protected string $migrationPath = 'schema/its4youmigrations/';
    protected array $migrationCommands = ['list', 'migrate', 'create', 'help'];
    protected string $helpMsg = '
            Specify command of migration! 
            e.g.: php bin/migrate.php list #.............................. to view prepared migration list
            e.g.: php bin/migrate.php migrate #........................... to execute migrations with confirmation
            e.g.: php bin/migrate.php migrate -y #........................ to execute migrations without confirmation
            e.g.: php bin/migrate.php migrate --y #....................... to execute migrations without confirmation
            e.g.: php bin/migrate.php create #..... to create migration file in /base migrations folder
            e.g.: php bin/migrate.php create -c #.. to create migration file in /customer migrations folder
            e.g.: php bin/migrate.php create --c #. to create migration file in /customer migrations folder
            to display help see: php bin/migrate.php help';

    /**
     * @param array $arg - Array of arguments passed to script
     */
    public function __construct($arg)
    {
        if (!is_array($arg) || empty($arg)) {
            $this->makeAborting($this->helpMsg);
        }

        require_once('include/utils/utils.php');
        require_once('include/logging.php');
        require_once('include/Migration/AbstractMigrations.php');

        require_once('modules/com_vtiger_workflow/include.inc');
        require_once('modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc');
        require_once('modules/com_vtiger_workflow/VTEntityMethodManager.inc');

        $this->db = PearDatabase::getInstance();
        $this->migrationsDatabaseWrapper = new MigrationsDatabaseWrapper();

        if (is_countable($arg) && count($arg) > 0) {
            try {
                $this->parseArg($arg);
                $this->handleMigration();
            } catch (Exception $objEx) {
                $this->makeAborting('Migration does not work!');
            }
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

        if (empty($filesToRun)) {
            $this->showMsg('No files available to migrate');
        } else {
            foreach ($filesToRun as $strFileName) {
                $this->showMsg($strFileName . ' ... [' . $done . '/' . (is_countable($filesToRun) ? count($filesToRun) : 0) . ']');
                $done++;
            }
        }
    }

    /**
     * Check migration table and run migration scripts
     *
     * @return void
     */
    public function run(): void
    {
        $this->migrationsDatabaseWrapper->checkMigrationTable();

        $this->runMigrations();
    }

    /**
     * @param array $arg
     *
     * @return void
     */
    protected function parseArg(array $arg): void
    {
        if (!isset($arg[1]) || empty($arg[1])) {
            $this->makeAborting('Specify command of migration');
        }

        if (!in_array($arg[1], $this->migrationCommands)) {
            $this->makeAborting('Command handler not found!');
        }

        $this->command = $arg[1];

        if (('create' === $arg[1]) && in_array($arg[3], ['-c', '--c'])) {
            $this->migrationPath .= 'customer/';
        }
    }

    /**
     * @return void
     */
    protected function handleMigration(): void
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
                $this->createMigrationFile();
                break;
            case 'help':
                $this->makeAborting($this->helpMsg);
                break;
        }
    }

    /**
     * Run migrations
     *
     * @return void
     */
    protected function runMigrations(): void
    {
        $filesToRun = $this->loadMigrationFiles();

        if (empty($filesToRun)) {
            $this->showMsg('No files available to migrate');
        }

        //for command '--y' and '-y'
        $optCommand = $this->commandType;

        if (!in_array($optCommand, ['-y', '--y'])) {
            $this->showMsg((is_countable($filesToRun) ? count($filesToRun) : 0) . ' migration-file(s) will be upgraded');

            $this->showMsg('Do you want to RUN these files?' . "\n" . 'Type "yes" to continue: ');
            $line = fgets(STDIN);

            if (trim($line) !== 'yes') {
                $this->makeAborting();
            }

            $this->showMsg('continuing...');
        }

        $done = 1;
        $this->db->setDieOnError(true);

        foreach ($filesToRun as $strFileName) {
            $this->showMsg('Start - ' . $strFileName . ' ... [' . $done . '/' . (is_countable($filesToRun) ? count($filesToRun) : 0) . ']');
            $this->markMigration($strFileName);
            $this->migrate($strFileName);
            $this->showMsg('Done - ' . $strFileName . ' ... [' . $done . '/' . (is_countable($filesToRun) ? count($filesToRun) : 0) . ']');
            $done++;
        }

        $this->db->setDieOnError(false);

        $done_display = $done - 1;
        $this->showMsg(PHP_EOL . 'Migrations done: ' . $done_display . file(s) . PHP_EOL);
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
            $allRunnedFiles = $this->migrationsDatabaseWrapper->loadRunFiles();

            foreach ($allMigrationFiles as $fileName) {
                if (!in_array($fileName, $allRunnedFiles)) {
                    $filesToRun[] = $fileName;
                }
            }
        }

        return $filesToRun;
    }

    /**
     * Load all migration Files
     * Return Array of all migration file Names
     *
     * @return array
     */
    protected function loadAllMigrationFiles(): array
    {
        if (!file_exists($this->migrationPath)) {
            $this->showMsg('Path ' . $this->migrationPath . ' does not exist!');

            return [];
        }

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

        asort($allMigrationFilesArray);

        return $allMigrationFilesArray;
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
     * Set status for migration file
     *
     * Recognized values:
     * 0 - created
     * 1 - finished
     * 2 - inprogress
     *
     * @param string $fileName
     * @param int    $migrationStatus
     *
     * @return void
     */
    protected function markMigration(string $fileName, int $migrationStatus = 0): void
    {
        $this->migrationsDatabaseWrapper->markMigration($fileName, $migrationStatus);
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
        $processMigration = true;

        if (!file_exists($fullFilePath)) {
            $this->showMsg('The migration-file ' . $strFileName . ' does not exist!');
            $processMigration = false;
        }

        $migrationClassName = 'ITS4YouMigration_' . str_replace('.php', '', basename($strFileName));
        require_once $fullFilePath;

        if (!class_exists($migrationClassName)) {
            $this->showMsg('The class-name for ' . $strFileName . ' is not correct!');
            $processMigration = false;
        }

        $migrationObj = new $migrationClassName();

        if (!is_subclass_of($migrationObj, 'AbstractMigrations')) {
            $this->showMsg('The class ' . $migrationClassName . ' is not extended from ITS4YouMigrationParent!');
            $processMigration = false;
        }

        if ($processMigration) {
            /** / migration created - mark as 2-inprogress / */
            $this->markMigration($strFileName, 2);
            /** / Start transaction / */
            $this->db->query('START TRANSACTION;');

            if (method_exists($migrationObj, 'migrate')) {
                $migrationObj->migrate($strFileName);
            } else {
                $this->showMsg('The class ' . $migrationClassName . ' has got no "migrate" method!');
            }

            /** / commit all db changes in migration / */
            $this->db->query('COMMIT;');
            /** / migration created - mark as 1-finished / */
            $this->markMigration($strFileName, 1);
        }
    }

    /**
     * @return void
     */
    protected function createMigrationFile(): void
    {
        $folderName = $this->migrationPath . date('Y') . DIRECTORY_SEPARATOR . date('m');

        if (!is_dir($folderName)) {
            if (!mkdir($folderName, 0755, true) && !is_dir($folderName)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $folderName));
            }
        }

        $migrationName = date('YmdHis');
        $fileName = $migrationName . '.php';
        $fullFileName = $folderName . '/' . $fileName;

        if (file_exists($fullFileName)) {
            $this->makeAborting('Migration file ' . $fullFileName . ' already exist!');
        }

        file_put_contents($fullFileName, $this->getTemplateContent($migrationName));
        $this->showMsg('created: ' . $migrationName);
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
     * Returns template of migration file
     *
     * @param string $migrationName
     *
     * @return string
     */
    protected function getTemplateContent(string $migrationName): string
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
}