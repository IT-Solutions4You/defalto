<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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