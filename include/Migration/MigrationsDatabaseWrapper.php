<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MigrationsDatabaseWrapper
{
    protected PearDatabase $db;
    protected string $migrationsTableName = 'its4you_migrations';

    public function __construct()
    {
        $this->db = PearDatabase::getInstance();
    }

    /**
     * method to check if migration table exists
     *
     * @return void
     */
    public function checkMigrationTable(): void
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
     * Load names of migration files which were run by migration script already
     *
     * @return array
     */
    public function loadRunFiles(): array
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
     * @param string $fileName
     * @param int    $migrationStatus
     *
     * @return void
     */
    public function markMigration(string $fileName, int $migrationStatus = 0): void
    {
        if ($fileName != '') {
            $this->db->pquery('REPLACE INTO its4you_migrations (migration_name,migration_createdtime,migration_status) VALUES (?,now(),?)', [$fileName, $migrationStatus]);
        }
    }
}