<?php

class ModComments_Install_Model extends Vtiger_Install_Model
{

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function getBlocks(): array
    {
        return [
            'LBL_MODCOMMENTS_INFORMATION' => [
                'commentcontent' => [
                    'uitype' => 19,
                    'column' => 'commentcontent',
                    'table' => 'vtiger_modcomments',
                    'generatedtype' => 1,
                    'label' => 'Comment',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'masseditable' => 2,
                    'entity_identifier' => 1,
                ],
            ],
            'LBL_OTHER_INFORMATION' => [
                'assigned_user_id' => [
                    'uitype' => '53',
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Assigned To',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'masseditable' => 2,
                ],
                'createdtime' => [
                    'uitype' => '70',
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Created Time',
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 0,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'modifiedtime' => [
                    'uitype' => '70',
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Modified Time',
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 0,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'related_to' => [
                    'uitype' => '10',
                    'column' => 'related_to',
                    'table' => 'vtiger_modcomments',
                    'generatedtype' => 1,
                    'label' => 'Related To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'masseditable' => 2,
                    'related_modules' => [
                        'Leads',
                        'Contacts',
                        'Accounts',
                    ],
                ],
                'creator' => [
                    'uitype' => '52',
                    'column' => 'smcreatorid',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Creator',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                ],
                'parent_comments' => [
                    'uitype' => '10',
                    'column' => 'parent_comments',
                    'table' => 'vtiger_modcomments',
                    'generatedtype' => 1,
                    'label' => 'Related To Comments',
                    'quickcreate' => 1,
                    'related_modules' => [
                        'ModComments',
                    ],
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_modcomments',
            'vtiger_modcommentscf',
        ];
    }

    public function installTables(): void
    {
        $this->getTable('vtiger_modcomments', null)
            ->createTable('modcommentsid')
            ->createColumn('commentcontent', 'text')
            ->createColumn('related_to', 'varchar(100) NOT NULL')
            ->createColumn('parent_comments', 'varchar(100) default NULL')
            ;

        $this->getTable('vtiger_modcommentscf', null)
            ->createTable('modcommentsid')
            ;
    }
}