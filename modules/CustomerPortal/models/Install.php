<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CustomerPortal_Install_Model extends Vtiger_Install_Model {

    /**
     * @var array
     * [Name, Link, BlockLabel]
     */
    public array $registerSettingsLinks = [
        ['LBL_CUSTOMER_PORTAL', 'index.php?module=CustomerPortal&view=Index&parent=Settings', 'LBL_OTHER_SETTINGS']
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateSettingsLinks();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateSettingsLinks(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [
            'vtiger_customerportal_fields',
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_customerportal_fields', null)
            ->createTable('tabid')
            ->createColumn('fieldid', 'int(19) default NULL')
            ->createColumn('visible', 'int(1) default NULL');
    }
}