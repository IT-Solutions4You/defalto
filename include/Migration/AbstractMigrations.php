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

abstract class AbstractMigrations
{
    use MigrationsTrait;

    public PearDatabase $db;

    protected string $wrongClassName = 'Missing class name: ITS4YouMigration_';

    /**
     * ITS4YouMigrationParent constructor.
     */
    public function __construct()
    {
        $this->db = PearDatabase::getInstance();
    }

    /**
     * method to run migration scripts
     *
     * @param string $fileName
     *
     * @return void
     */
    public function migrate(string $fileName): void
    {
    }
}