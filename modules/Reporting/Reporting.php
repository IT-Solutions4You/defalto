<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting extends CRMEntity
{
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = [
        'df_reportingcf',
        'reportingid',
    ];
    /**
     * Used in class functions of CRMEntity
     */
    public string $moduleName = 'Reporting';
    public string $parentName = 'ANALYTICS';
    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = [
        'vtiger_crmentity',
        'df_reporting',
        'df_reportingcf',
    ];
    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'df_reporting'     => 'reportingid',
        'df_reportingcf'   => 'reportingid',
    ];
    public $table_index = 'reportingid';
    public $table_name = 'df_reporting';
    public $def_basicsearch_col = 'report_name';

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        $this->saveSharing();
    }
}