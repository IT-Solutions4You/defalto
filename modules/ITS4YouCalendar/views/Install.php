<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_Install_View extends Vtiger_Index_View
{
    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request): void
    {
        (new Settings_Vtiger_Index_View())->checkPermission($request);
    }

    /**
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true): void
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function postProcess(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        error_reporting(E_ALL);

        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $adb->setDieOnError(true);

        $moduleName = $request->getModule();
        $moduleFocus = CRMEntity::getInstance($moduleName);

        $entity = in_array(get_parent_class($moduleFocus), ['CRMEntity', 'Vtiger_CRMEntity']);
        $baseTableId = $moduleFocus->table_index;
        $baseTable = $moduleFocus->table_name;
        $label = $moduleFocus->moduleLabel;
        $name = $moduleFocus->moduleName;
        $parent = $moduleFocus->parentName;
        $version = 0.1;
        $blocks = self::getBlocks();

        if (!empty($entity) && empty($baseTableId)) {
            self::logError('Empty base table ID');
            die('Empty base table ID');
        }

        if (!empty($entity) && empty($baseTable)) {
            self::logError('Empty base table ID');
            die('Empty base table');
        }

        if (empty($label)) {
            self::logError('Dynamic created label');

            $label = str_replace('ITS', '', $name);
            $label = str_replace('4You', '', $name);
        }

        if (empty($cfTable)) {
            self::logError('Dynamic custom field table');

            $cfTable = $baseTable . 'cf';
        }

        if (empty($groupRelTable)) {
            self::logError('Dynamic group relation table');

            $groupRelTable = $baseTable . 'grouprel';
        }

        $crmVersion = Vtiger_Version::current();
        $deleteModule = Vtiger_Module::getInstance($name);

        if (!empty($deleteModule->basetable)) {
            self::logError('Drop tables');

            $dropTables = [
                $deleteModule->basetable,
                $deleteModule->basetable . '_seq',
                $cfTable,
                $cfTable . '_seq',
                $groupRelTable,
                $groupRelTable . '_seq',
            ];

            $adb->pquery('DELETE FROM vtiger_crmentity WHERE setype=?', [$name]);

            foreach ($dropTables as $dropTable) {
                $adb->pquery('DROP TABLE IF EXISTS ' . $dropTable);
            }
        }

        if ($deleteModule) {
            $adb->pquery('DELETE FROM vtiger_field WHERE tabid=?', [getTabid($name)]);
            $adb->pquery('DELETE FROM vtiger_blocks WHERE tabid=?', [getTabid($name)]);
            $adb->pquery('DELETE FROM vtiger_relatedlists WHERE tabid=? OR related_tabid=?', [getTabid($name), getTabid($name)]);

            $deleteModule->delete();
            self::logError('Delete module');
            die('Delete module');
        }

        self::logSuccess('Module creating');

        $moduleInstance = new Vtiger_Module();
        $moduleInstance->name = $name;
        $moduleInstance->parent = $parent;
        $moduleInstance->label = $label;
        $moduleInstance->version = $version;
        $moduleInstance->minversion = $crmVersion;
        $moduleInstance->maxversion = $crmVersion;
        $moduleInstance->isentitytype = $entity;
        $moduleInstance->basetable = $baseTable;
        $moduleInstance->basetableid = $baseTableId;
        $moduleInstance->save();

        self::logSuccess('Module created');

        $moduleInstance->setDefaultSharing();

        self::logSuccess('Sharing Access Setup');

        $moduleInstance->initWebservice();

        self::logSuccess('Webservice Setup');

        if ($entity) {
            $moduleInstance->initTables($moduleInstance->basetable, $moduleInstance->basetableid);

            Vtiger_Filter::deleteForModule($moduleInstance);

            $newFilter = new Vtiger_Filter();
            $newFilter->name = 'All';
            $newFilter->isdefault = true;
            $filterSequence = 0;
            $moduleInstance->addFilter($newFilter);

            if (isset($blocks['LBL_ITEM_DETAILS'])) {
                $taxResult = $adb->pquery('SELECT * FROM vtiger_inventorytaxinfo');

                while ($row = $adb->fetchByAssoc($taxResult)) {
                    $blocks['LBL_ITEM_DETAILS'][$row['taxname']] = array(
                        'table' => 'vtiger_inventoryproductrel',
                        'label' => $row['taxlabel'],
                        'uitype' => 83,
                        'typeofdata' => 'V~O',
                        'displaytype' => 5,
                        'masseditable' => 0,
                    );
                }
            }

            foreach ($blocks as $block => $fields) {
                self::logSuccess('Block create: ' . $block);

                $newBlock = new Vtiger_Block();
                $newBlock->label = $block;
                $moduleInstance->addBlock($newBlock);

                foreach ($fields as $fieldName => $fieldParams) {
                    self::logSuccess('Field create: ' . $fieldName);

                    $relatedModules = array();
                    $picklistValues = array();

                    $fieldInstance = new Vtiger_Field();
                    $fieldInstance->name = $fieldName;
                    $fieldInstance->column = $fieldName;
                    $fieldInstance->table = $baseTable;
                    $fieldInstance->masseditable = 0;
                    $fieldInstance->quickcreate = 0;

                    foreach ($fieldParams as $fieldParamName => $fieldParam) {
                        if ('picklist_values' === $fieldParamName) {
                            $picklistValues = $fieldParam;
                        } elseif ('related_modules' === $fieldParamName) {
                            $relatedModules = $fieldParam;
                        } else {
                            $fieldInstance->$fieldParamName = $fieldParam;
                        }
                    }

                    $newBlock->addField($fieldInstance);

                    if (!empty($picklistValues)) {
                        $adb->query('DROP TABLE IF EXISTS vtiger_' . $fieldName);
                        $adb->pquery('DELETE FROM vtiger_picklist WHERE name=?', [$fieldName]);

                        self::logSuccess('Picklist values create: ' . $fieldName . ' - ' . implode(',', $picklistValues));

                        $fieldInstance->setPicklistValues($picklistValues);
                    }

                    if (!empty($relatedModules)) {
                        self::logSuccess('Related modules create: ' . $fieldName . ' - ' . implode(',', $relatedModules));

                        foreach ($relatedModules as $relatedModule => $relatedLabel) {
                            if (!is_numeric($relatedModule)) {
                                $relModule = Vtiger_Module::getInstance($relatedModule);
                                $relModule->unsetRelatedList($moduleInstance, $relatedLabel, 'get_dependents_list');
                            }

                            $fieldInstance->setRelatedModules([$relatedModule => $relatedLabel]);
                        }
                    }

                    if (isset($fieldParams['filter'])) {
                        self::logSuccess('Filter create: ' . $fieldName);

                        $filterSequence++;
                        $newFilter->addField($fieldInstance, $filterSequence);
                    }

                    if (isset($fieldParams['entity_identifier'])) {
                        self::logSuccess('Entity Identifier: ' . $fieldName);

                        $moduleInstance->setEntityIdentifier($fieldInstance);
                    }
                }
            }

            self::logSuccess('Link start creating');

            if (!empty($name) && !empty($parent)) {
                Settings_MenuEditor_Module_Model::updateModuleApp($name, $parent);
                Settings_MenuEditor_Module_Model::addModuleToApp($name, $parent);

                self::logSuccess('Link created');
            } else {
                self::logError('Link not created');
            }
        }

        $moduleManagerModel = new Settings_ModuleManager_Module_Model();
        $moduleManagerModel->disableModule($moduleName);
        $moduleManagerModel->enableModule($moduleName);

        self::logSuccess('Module result: ' . $moduleName);
        self::logSuccess($moduleInstance);
    }

    /**
     * @return array
     */
    public static function getBlocks(): array
    {
        return [
            'LBL_BASIC_INFORMATION' => [
                'subject' => array(
                    'label' => 'Subject',
                    'columntype' => 'VARCHAR(255)',
                    'uitype' => 2,
                    'typeofdata' => 'V~M',
                    'summaryfield' => 1,
                    'filter' => 1,
                    'entity_identifier' => 1,
                ),
                'assigned_user_id' => array(
                    'column' => 'smownerid',
                    'label' => 'Assigned To',
                    'uitype' => 53,
                    'typeofdata' => 'V~M',
                    'summaryfield' => 1,
                    'table' => 'vtiger_crmentity',
                    'filter' => 1,
                ),
                'datetime_start' => array(
                    'column' => 'datetime_start',
                    'label' => 'Start Date & Time',
                    'uitype' => 6,
                    'typeofdata' => 'DT~M',
                    'columntype' => 'datetime',
                    'filter' => 1,
                ),
                'datetime_end' => array(
                    'column' => 'datetime_end',
                    'label' => 'End Date & Time',
                    'uitype' => 6,
                    'typeofdata' => 'DT~M~time_end',
                    'columntype' => 'datetime',
                    'filter' => 1,
                ),
                'is_all_day' => array(
                    'column' => 'is_all_day',
                    'label' => 'Is All Day',
                    'uitype' => 56,
                    'typeofdata' => 'C~O',
                    'columntype' => 'VARCHAR(3)',
                ),
                'calendar_status' => array(
                    'column' => 'status',
                    'label' => 'Status',
                    'uitype' => 15,
                    'typeofdata' => 'V~M',
                    'picklist_values' => [
                        'Planned',
                        'Completed',
                        'Cancelled',
                    ],
                    'columntype' => 'VARCHAR(200)',
                    'filter' => 1,

                ),
                'calendar_priority' => array(
                    'column' => 'priority',
                    'label' => 'Priority',
                    'uitype' => 15,
                    'typeofdata' => 'V~O',
                    'picklist_values' => [
                        'High',
                        'Medium',
                        'Low',
                    ],
                    'columntype' => 'VARCHAR(200)',
                    'filter' => 1,

                ),
                'calendar_type' => array(
                    'column' => 'type',
                    'label' => 'Type',
                    'uitype' => 15,
                    'typeofdata' => 'V~O',
                    'picklist_values' => [
                        'Call',
                        'Meeting',
                        'Email',
                        'Reminder',
                    ],
                    'columntype' => 'VARCHAR(200)',
                ),
                'calendar_visibility' => array(
                    'column' => 'visibility',
                    'label' => 'Visibility',
                    'uitype' => 16,
                    'typeofdata' => 'V~O',
                    'picklist_values' => [
                        'Private',
                        'Public',
                    ],
                    'columntype' => 'VARCHAR(50)',
                ),
                'send_notification' => array(
                    'column' => 'send_notification',
                    'label' => 'Send Notification',
                    'uitype' => 56,
                    'typeofdata' => 'C~O',
                    'columntype' => 'VARCHAR(3)',
                ),
                'duration_hours' => array(
                    'column' => 'duration_hours',
                    'label' => 'Duration',
                    'displaytype' => 3,
                    'uitype' => 63,
                    'typeofdata' => 'T~O',
                    'columntype' => 'VARCHAR(200)',
                ),
                'duration_minutes' => array(
                    'column' => 'duration_minutes',
                    'label' => 'Duration Minutes',
                    'displaytype' => 3,
                    'uitype' => 16,
                    'typeofdata' => 'T~O',
                    'columntype' => 'VARCHAR(200)',
                ),
                'location' => array(
                    'column' => 'location',
                    'label' => 'Location',
                    'uitype' => 1,
                    'typeofdata' => 'V~O',
                    'columntype' => 'VARCHAR(150)',
                ),
            ],
            'LBL_REMINDER_INFORMATION' => [
                'reminder_time' => array(
                    'column' => 'reminder_time',
                    'table' => 'its4you_remindme',
                    'label' => 'Send Reminder',
                    'uitype' => 30,
                    'typeofdata' => 'I~O',
                    'columntype' => 'VARCHAR(150)',
                ),
            ],
            'LBL_RECURENCE_INFORMATION' => [
                'recurring_type' => array(
                    'column' => 'recurring_type',
                    'label' => 'Recurrence',
                    'uitype' => 16,
                    'typeofdata' => 'O~O',
                    'columntype' => 'VARCHAR(200)',
                    'picklist_values' => [
                        'Daily',
                        'Weekly',
                        'Monthly',
                        'Yearly',
                    ],
                ),
            ],
            'LBL_REFERENCE_INFORMATION' => [
                'parent_id' => array(
                    'column' => 'parent_id',
                    'label' => 'Related To',
                    'uitype' => 10,
                    'typeofdata' => 'I~O',
                    'related_modules' => [
                        'Campaigns',
                        'HelpDesk',
                        'Leads',
                        'Potentials',
                    ],
                    'columntype' => 'INT(11)',
                ),
                'contact_id' => array(
                    'column' => 'contact_id',
                    'label' => 'Contact Name',
                    'uitype' => 57,
                    'typeofdata' => 'I~O',
                    'related_modules' => [
                        'Contacts'
                    ],
                    'columntype' => 'INT(11)',
                ),
                'account_id' => array(
                    'column' => 'account_id',
                    'label' => 'Account Name',
                    'uitype' => 10,
                    'typeofdata' => 'I~O',
                    'related_modules' => [
                        'Accounts',
                    ],
                    'columntype' => 'INT(11)',
                ),
            ],
            'LBL_CUSTOM_INFORMATION' => [
                'its4you_calendar_no' => array(
                    'label' => 'Calendar No',
                    'uitype' => 4,
                    'typeofdata' => 'V~O',
                ),
                'source' => array(
                    'label' => 'Source',
                    'uitype' => 1,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                    'summaryfield' => 1,
                ),
                'creator' => array(
                    'column' => 'smcreatorid',
                    'label' => 'Creator',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ),
                'createdtime' => array(
                    'label' => 'Created Time',
                    'uitype' => 70,
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                    'headerfield' => 1,
                ),
                'modifiedby' => array(
                    'label' => 'Last Modified By',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ),
                'modifiedtime' => array(
                    'label' => 'Modified Time',
                    'uitype' => 70,
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ),
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => array(
                    'label' => 'Description',
                    'columntype' => 'text',
                    'uitype' => 19,
                    'typeofdata' => 'V~O',
                    'summaryfield' => 0,
                    'table' => 'vtiger_crmentity',
                ),
            ],
        ];
    }

    /**
     * @param mixed $message
     * @return void
     */
    public static function logError(mixed $message): void
    {
        echo '<pre style="font-size: 20px; color: red;">' . print_r($message, true) . '</pre>';
    }

    /**
     * @param mixed $message
     * @return void
     */
    public static function logSuccess(mixed $message): void
    {
        echo '<pre style="font-size: 20px; color: darkolivegreen;">' . print_r($message, true) . '</pre>';
    }
}