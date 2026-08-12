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

    public array $relatedListFields = [['report_name', 'primary_module', 'folder', 'description', 'max_entries',]];
    public array $popupFields = ['report_name', 'primary_module', 'folder', 'description', 'max_entries',];

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function installModule()
    {
        $isNewModule = !Vtiger_Module::getInstance($this->moduleName);

        parent::installModule();

        if ($isNewModule) {
            $moduleInstance = Vtiger_Module::getInstance($this->moduleName);

            if ($moduleInstance) {
                $moduleInstance->setDefaultSharing('Private');
            }
        }
    }

    public function retrieveBlocks(): void
    {
        self::$fieldsConfig['Reporting'] = $this->getBlocks();
    }

    /**
     * @throws Exception
     */
    public function createField(string $fieldName, array $fieldParams): Vtiger_Field_Model|bool
    {
        $fieldInstance = parent::createField($fieldName, $fieldParams);

        if (in_array($fieldName, ['chart_type', 'chart_position', 'chart_config'], true) && $fieldInstance && !empty($fieldParams['block'])) {
            $blockInstance = $fieldParams['block'];
            $fieldInstance->block = $blockInstance;
            $fieldInstance->getFieldTable()->updateData(
                ['block' => $blockInstance->id],
                ['fieldid' => $fieldInstance->id],
            );
        }

        if ('sharing_type' === $fieldName && $fieldInstance) {
            $this->setSharingTypeFieldSequence($fieldInstance);
        }

        return $fieldInstance;
    }

    protected function setSharingTypeFieldSequence(Vtiger_Field_Model $sharingTypeField): void
    {
        $sharingField = $this->getFieldInstance('sharing');

        if (!$sharingField->getId()) {
            return;
        }

        $sharingSequence = (int)$sharingField->get('sequence');
        $sharingTypeSequence = (int)$sharingTypeField->get('sequence');

        if ($sharingTypeSequence < $sharingSequence) {
            return;
        }

        $sharingTypeField->getFieldTable()->updateData(
            ['sequence' => $sharingSequence],
            ['fieldid' => $sharingTypeField->getId()],
        );
        $sharingField->getFieldTable()->updateData(
            ['sequence' => $sharingSequence + 1],
            ['fieldid' => $sharingField->getId()],
        );
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
                        'tabular',
                        'summary',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 2,
                    'typeofdata' => 'V~M',
                    'ajaxeditable' => 0,
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
                    'ajaxeditable' => 0,
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
                'currency_id' => [
                    'label' => 'Currency',
                    'uitype' => 117,
                    'typeofdata' => 'I~M',
                ],
                'group_by_currency' => [
                    'label' => 'Group calculations by currency',
                    'uitype' => 56,
                    'typeofdata' => 'C~O',
                    'defaultvalue' => 0,
                ],
                'conversion_rate' => [
                    'label' => 'Conversion Rate',
                    'uitype' => 1,
                    'presence' => Vtiger_Field_Model::PRESENCE_HIDDEN,
                    'typeofdata' => 'N~O',
                ],
            ],
            'LBL_COLUMNS' => [
                'fields' => [
                    'columntype' => 'TEXT',
                    'column' => 'fields',
                    'label' => 'Fields',
                    'table' => 'df_reporting',
                    'ajaxeditable' => 0,
                ],
                'sort_by' => [
                    'columntype' => 'TEXT',
                    'column' => 'sort_by',
                    'label' => 'Sort By',
                    'table' => 'df_reporting',
                    'ajaxeditable' => 0,
                ],
                'labels' => [
                    'columntype' => 'TEXT',
                    'column' => 'labels',
                    'label' => 'Labels',
                    'table' => 'df_reporting',
                    'ajaxeditable' => 0,
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
                    'ajaxeditable' => 0,
                ],
            ],
            'LBL_GROUPING' => [
                'group_by' => [
                    'columntype' => 'TEXT',
                    'column' => 'group_by',
                    'label' => 'Group By',
                    'table' => 'df_reporting',
                    'ajaxeditable' => 0,
                ],
            ],
            'LBL_CHARTS' => [
                'chart_type' => [
                    'column' => 'chart_type',
                    'label' => 'Chart Type',
                    'table' => 'df_reporting',
                    'uitype' => 15,
                    'picklist_values' => [
                        'bar',
                        'line',
                        'pie',
                        'doughnut',
                    ],
                    'defaultvalue' => 'bar',
                    'ajaxeditable' => 0,
                ],
                'chart_position' => [
                    'column' => 'chart_position',
                    'label' => 'Chart Position',
                    'table' => 'df_reporting',
                    'uitype' => 15,
                    'picklist_values' => [
                        'above',
                        'below',
                    ],
                    'defaultvalue' => 'above',
                    'ajaxeditable' => 0,
                ],
                'chart_config' => [
                    'columntype' => 'TEXT',
                    'column' => 'chart_config',
                    'label' => 'Chart Axes',
                    'table' => 'df_reporting',
                    'ajaxeditable' => 0,
                ],
            ],
            'LBL_FILTERS' => [
                'filter' => [
                    'columntype' => 'TEXT',
                    'column' => 'filter',
                    'label' => 'Filter',
                    'table' => 'df_reporting',
                    'ajaxeditable' => 0,
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
                'sharing_type' => [
                    'column' => 'sharing_type',
                    'label' => 'Sharing Access',
                    'table' => 'df_reporting',
                    'uitype' => 15,
                    'picklist_values' => [
                        'private',
                        'all',
                        'selected',
                    ],
                    'defaultvalue' => 'private',
                    'typeofdata' => 'V~M',
                    'ajaxeditable' => 0,
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
        if (Vtiger_Utils::CheckTable('df_reporting')) {
            $this->getTable('df_reporting', 'reportingid')
                ->createColumn('group_by', 'TEXT')
                ->createColumn('chart_config', 'TEXT')
                ->createColumn('chart_position', 'VARCHAR(20) NOT NULL DEFAULT \'above\'')
                ->createColumn('currency_id', 'INT(19) DEFAULT NULL')
                ->createColumn('group_by_currency', 'TINYINT(1) NOT NULL DEFAULT 0')
                ->createColumn('conversion_rate', 'DECIMAL(25,8) DEFAULT NULL')
                ->createColumn('sharing_type', 'VARCHAR(20) NOT NULL DEFAULT \'selected\'')
            ;
        }

        $this->createPicklistTable('vtiger_primary_module', 'primary_moduleid', 'primary_module');
        $this->createPicklistTable('vtiger_folder', 'folderid', 'folder');
        $this->createPicklistTable('vtiger_sharing_type', 'sharing_typeid', 'sharing_type');
        $this->createPicklistTable('vtiger_sharing', 'sharingid', 'sharing');
    }
}
