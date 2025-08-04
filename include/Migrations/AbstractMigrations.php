<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'include/Migrations/MigrationsTrait.php';

abstract class AbstractMigrations
{
    use MigrationsTrait;

    public PearDatabase $db;

    protected string $wrongClassName = 'Missing class name: Migration_';

    public function __construct()
    {
        $this->db = PearDatabase::getInstance();
    }

    /**
     * @param string $fileName
     *
     * @return void
     */
    public function migrate(string $fileName): void
    {
    }
}