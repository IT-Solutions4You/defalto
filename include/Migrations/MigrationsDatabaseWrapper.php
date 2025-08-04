<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MigrationsDatabaseWrapper
{
    protected PearDatabase $db;
    protected string $migrationsTableName = 'its4you_migrations';

    public function __construct()
    {
        $this->db = PearDatabase::getInstance();
        $this->checkMigrationTable();
    }

    /**
     * Load names of migration files which were run by migration script already
     *
     * @return array
     */
    public function loadRunFiles(): array
    {
        $result = $this->db->pquery('SELECT migration_name FROM ' . $this->migrationsTableName . ' WHERE migration_status = ?', [1]);
        $allRunFiles = [];

        while ($migrationRow = $this->db->fetchByAssoc($result)) {
            $allRunFiles[] = $migrationRow['migration_name'];
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
        if ('' !== $fileName) {
            $this->db->pquery(
                'REPLACE INTO ' . $this->migrationsTableName . ' (migration_name,migration_createdtime,migration_status) VALUES (?,now(),?)',
                [$fileName, $migrationStatus]
            );
        }
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
}