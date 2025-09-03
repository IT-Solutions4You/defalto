<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Install_Model extends Core_Install_Model
{
    protected string $moduleName = 'Reporting';
    protected string $parentName = 'Tools';

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function retrieveBlocks(): void
    {
        self::$fieldsConfig['Reporting'] = $this->getBlocks();
    }

    public function getBlocks(): array
    {
        return [
            'LBL_TABS' => [
                'report_type' => [
                    'column' => 'report_type',
                    'label' => 'Report Type',
                    'table' => 'df_reporting',
                    'uitype' => 15,
                    'picklist_values' => [
                        'tabular'
                    ],
                    'filter' => 1,
                    'filter_sequence' => 2,
                    'typeofdata' => 'V~M',
                ],
                'primary_module' => [
                    'column' => 'primary_module',
                    'label' => 'Primary module',
                    'table' => 'df_reporting',
                    'uitype' => 15,
                    'picklist_values' => [],
                    'filter' => 1,
                    'filter_sequence' => 3,
                    'typeofdata' => 'V~M',
                    'headerfield' => 1,
                ],
            ],
            'LBL_DETAILS' => [
                'report_name' => [
                    'uitype' => 2,
                    'column' => 'report_name',
                    'table' => 'df_reporting',
                    'label' => 'Report Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'folder' => [
                    'column' => 'folder',
                    'label' => 'Folder',
                    'table' => 'df_reporting',
                    'uitype' => 15,
                    'picklist_values' => [],
                    'filter' => 1,
                    'filter_sequence' => 4,
                    'headerfield' => 1,
                ],
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'filter' => 1,
                    'filter_sequence' => 5,
                    'headerfield' => 1,
                ],
                'max_entries' => [
                    'uitype' => 7,
                    'column' => 'max_entries',
                    'label' => 'Max Entries',
                    'table' => 'df_reporting',
                    'headerfield' => 1,
                ],
            ],
            'LBL_COLUMNS' => [
                'fields' => [
                    'columntype' => 'TEXT',
                    'column' => 'fields',
                    'label' => 'Fields',
                    'table' => 'df_reporting',
                ],
                'sort_by' => [
                    'columntype' => 'TEXT',
                    'column' => 'sort_by',
                    'label' => 'Sort By',
                    'table' => 'df_reporting',
                ],
                'labels' => [
                    'columntype' => 'TEXT',
                    'column' => 'labels',
                    'label' => 'Labels',
                    'table' => 'df_reporting',
                ],
                'width' => [
                    'columntype' => 'TEXT',
                    'column' => 'width',
                    'label' => 'Width',
                    'table' => 'df_reporting',
                ],
                'align' => [
                    'columntype' => 'TEXT',
                    'column' => 'align',
                    'label' => 'Align',
                    'table' => 'df_reporting',
                ],
            ],
            'LBL_CALCULATIONS' => [
                'calculation' => [
                    'columntype' => 'TEXT',
                    'column' => 'calculation',
                    'label' => 'Calculation',
                    'table' => 'df_reporting',
                ],
            ],
            'LBL_FILTERS' => [
                'filter' => [
                    'columntype' => 'TEXT',
                    'column' => 'filter',
                    'label' => 'Filter',
                    'table' => 'df_reporting',
                ],
            ],
            'LBL_SHARING' => [
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 6,
                ],
                'sharing' => [
                    'uitype' => 33,
                    'columntype' => 'TEXT',
                    'column' => 'sharing',
                    'label' => 'Sharing',
                    'table' => 'df_reporting',
                ],
            ],
            'LBL_RENDERED_TABLE' => [
                'rendered_table' => [
                    'columntype' => 'VARCHAR(100)',
                    'column' => 'rendered_table',
                    'label' => 'rendered_table',
                    'table' => 'df_reporting',
                ]
            ],
        ];
    }

    public function getTables(): array
    {
        return [
            'df_reporting',
            'df_reportingcf',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->createPicklistTable('vtiger_primary_module', 'primary_moduleid', 'primary_module');
        $this->createPicklistTable('vtiger_folder', 'folderid', 'folder');
        $this->createPicklistTable('vtiger_sharing', 'sharingid', 'sharing');
    }
}