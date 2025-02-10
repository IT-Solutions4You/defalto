<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Core_Install_Model extends Core_DatabaseData_Model
{
    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = [];
    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [];
    /**
     * @var array
     * [events, file, class, condition, dependOn, modules]
     */
    public array $registerEventHandler = [];

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [];
    /**
     * @var array
     * [Name, Link, BlockLabel]
     */
    public array $registerSettingsLinks = [];
    /**
     * @var array
     * [Module, Workflow Name, Workflow Label, Modules JSON]
     */
    public array $registerWorkflows = [];
    /**
     * @var PearDatabase
     */
    protected PearDatabase $db;
    /**
     * @var string
     */
    protected string $eventType = '';
    /**
     * @var string
     */
    protected string $moduleName = '';
    /**
     * @var string
     */
    protected string $moduleNumbering = '';
    /**
     * @var string
     */
    protected string $parentName = '';

    /**
     * @var array
     */
    public static array $filters = [];

    /**
     * @var array
     */
    public static array $blocks = [];

    /**
     * @var array
     */
    public static array $installedModules = [];

    /**
     * @return void
     */
    abstract public function addCustomLinks(): void;

    /**
     * @return void
     * @throws Exception
     */
    public function addModuleToCustomerPortal()
    {
        $projectTabId = getTabid($this->moduleName);

        // Add module to Customer portal
        if (getTabid('CustomerPortal') && $projectTabId) {
            $checkAlreadyExists = $this->db->pquery('SELECT 1 FROM vtiger_customerportal_tabs WHERE tabid=?', [$projectTabId]);

            if ($checkAlreadyExists && $this->db->num_rows($checkAlreadyExists) < 1) {
                $sequenceQuery = $this->db->pquery('SELECT max(sequence) as maxsequence FROM vtiger_customerportal_tabs', []);
                $sequence = (int)$this->db->query_result($sequenceQuery, 0, 'maxsequence') + 1;

                $this->db->pquery('INSERT INTO vtiger_customerportal_tabs(tabid,visible,sequence) VALUES (?, ?, ?)', [$projectTabId, 1, $sequence]);
                $this->db->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [$projectTabId, 'showrelatedinfo', 1]);
            }
        }
    }

    /**
     * @return void
     */
    abstract public function deleteCustomLinks(): void;

    /**
     * @return void
     * @throws AppException
     */
    public function deleteModule(): void
    {
        $moduleName = $this->moduleName;
        $moduleInstance = Vtiger_Module::getInstance($moduleName);
        $moduleFocus = CRMEntity::getInstance($moduleName);

        if ($moduleInstance) {
            self::logError('Delete records, fields, blocks, related lists');
            $this->db->pquery('DELETE FROM vtiger_crmentity WHERE setype=?', [$moduleName]);
            $this->db->pquery('DELETE FROM vtiger_field WHERE tabid=?', [getTabid($moduleName)]);
            $this->db->pquery('DELETE FROM vtiger_blocks WHERE tabid=?', [getTabid($moduleName)]);
            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE tabid=? OR related_tabid=?', [getTabid($moduleName), getTabid($moduleName)]);

            self::logError('Delete picklist values');
            $picklistTables = [];

            foreach ($this->getBlocks() as $blockName => $blockInfo) {
                foreach ($blockInfo as $fieldName => $fieldInfo) {
                    if (!empty($fieldInfo['picklist_values'])) {
                        self::logError('Delete picklist values: ' . $fieldName);

                        $picklistTables[] = 'vtiger_' . $fieldName;
                        $this->db->pquery('DELETE FROM vtiger_picklist WHERE name=?', [$fieldName]);
                    }
                }
            }

            self::logError('Delete module tables');
            $moduleTables = [];

            if (!empty($moduleInstance->basetable)) {
                $cfTable = $moduleFocus->customFieldTable[0];
                $moduleTables = [
                    $moduleInstance->basetable,
                    $moduleInstance->basetable . '_seq',
                    $cfTable,
                    $cfTable . '_seq',
                ];
            }

            self::logError('Delete filter');
            Vtiger_Filter::deleteForModule($moduleInstance);

            self::logError('Delete module');
            $moduleInstance->delete();

            self::logError('Drop tables');
            $dropTables = array_merge($this->getTables(), $moduleTables, $picklistTables);

            self::logSuccess($dropTables);

            $this->db->pquery('SET FOREIGN_KEY_CHECKS=0');

            foreach ($dropTables as $dropTable) {
                $this->db->pquery('DROP TABLE IF EXISTS ' . $dropTable);
            }

            self::logSuccess('Module deleted');
        }
    }

    /**
     * @return array
     * @throws AppException
     */
    abstract public function getBlocks(): array;

    /**
     * @param string $eventType
     * @param string $moduleName
     * @return self
     */
    public static function getInstance(string $eventType, string $moduleName): self
    {
        $class = $moduleName . '_Install_Model';

        $instance = new $class();
        $instance->eventType = $eventType;
        $instance->moduleName = $moduleName;
        $instance->db = PearDatabase::getInstance();

        return $instance;
    }

    /**
     * @return array
     */
    abstract public function getTables(): array;

    /**
     * @throws AppException
     */
    public function install()
    {
        require_once 'include/utils/utils.php';
        require_once 'vtlib/Vtiger/Module.php';
        require_once 'vtlib/Vtiger/Cron.php';
        require_once 'modules/ModComments/ModComments.php';
        require_once 'modules/ModTracker/ModTracker.php';

        switch ($this->eventType) {
            case 'module.postinstall':
            case 'module.enabled':
            case 'module.postupdate':
                $this->addCustomLinks();
                break;
            case 'module.disabled':
            case 'module.preuninstall':
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
        }
    }

    /**
     * @return bool
     */
    public function isInstalledModule(): bool
    {
        if (isset(self::$installedModules[$this->moduleName])) {
            self::logError($this->moduleName . ': was already installed in this process');

            return true;
        }

        self::$installedModules[$this->moduleName] = true;

        return false;
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installModule()
    {
        if ($this->isInstalledModule()) {
            return;
        }

        $this->installTables();

        self::logSuccess('Install tables');

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $this->moduleName;
        $moduleFocus = CRMEntity::getInstance($moduleName);

        $entity = !empty($moduleFocus->isEntity) ? 1 : 0;
        $baseTableId = $moduleFocus->table_index ?? '';
        $baseTable = $moduleFocus->table_name ?? '';
        $label = $moduleFocus->moduleLabel ?? '';
        $name = $moduleFocus->moduleName ?? '';
        $parent = $moduleFocus->parentName ?? '';
        $cfTable = $moduleFocus->customFieldTable[0] ?? '';
        $groupRelTable = $moduleFocus->groupFieldTable[0] ?? '';

        $versionClass = $moduleName . '_Version_Helper';
        $version = class_exists($versionClass) ? $versionClass::getVersion() : 0.1;

        $blocks = $this->getBlocks();

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
            $label = str_replace('4You', '', $label);
        }

        if (!empty($baseTable) && empty($cfTable)) {
            self::logError('Dynamic custom field table');

            $cfTable = $baseTable . 'cf';
        }

        if (!empty($baseTable) && empty($groupRelTable)) {
            self::logError('Dynamic group relation table');

            $groupRelTable = $baseTable . 'grouprel';
        }

        $crmVersion = Vtiger_Version::current();
        $moduleInstance = Vtiger_Module::getInstance($name);

        if (!$moduleInstance) {
            $moduleInstance = new Vtiger_Module();

            self::logSuccess('Module creating');
        } else {
            self::logSuccess('Module Updating');
        }

        $moduleInstance->name = $name;
        $moduleInstance->parent = $parent;
        $moduleInstance->label = $label;
        $moduleInstance->version = $version;
        $moduleInstance->minversion = $crmVersion;
        $moduleInstance->maxversion = $crmVersion;
        $moduleInstance->isentitytype = $entity;
        $moduleInstance->basetable = $baseTable;
        $moduleInstance->basetableid = $baseTableId;
        $moduleInstance->customtable = $cfTable;
        $moduleInstance->grouptable = $groupRelTable;
        $moduleInstance->save();

        self::logSuccess('Module created');

        $moduleInstance->setDefaultSharing();

        self::logSuccess('Sharing Access Setup');

        $moduleInstance->initWebservice();

        self::logSuccess('Webservice Setup');

        if ($entity) {
            $moduleInstance->initTables($moduleInstance->basetable, $moduleInstance->basetableid);
            $entityIdentifiers = [];
            $filterFields = [];
            $filterDynamicSequence = 0;

            $fieldTable = $this->getTable('vtiger_field', null);

            foreach ($blocks as $block => $fields) {
                self::logSuccess('Block create: ' . $block);
                $fieldSequence = 0;
                $blockInstance = $this->createBlock($block, $moduleInstance);

                foreach ($fields as $fieldName => $fieldParams) {
                    if (empty($fieldName)) {
                        continue;
                    }

                    self::logSuccess('Field create: ' . $fieldName);

                    $fieldSequence++;
                    $relatedModules = [];
                    $picklistValues = [];

                    $fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleInstance);

                    if (!$fieldInstance) {
                        $fieldInstance = new Vtiger_Field_Model();
                    }

                    $fieldInstance->name = $fieldName;
                    $fieldInstance->column = $fieldName;
                    $fieldInstance->table = $baseTable;
                    $fieldInstance->sequence = $fieldSequence;

                    foreach ($fieldParams as $fieldParamName => $fieldParam) {
                        if ('picklist_values' === $fieldParamName) {
                            $picklistValues = $fieldParam;
                        } elseif ('related_modules' === $fieldParamName) {
                            $relatedModules = $fieldParam;
                        } else {
                            $fieldInstance->$fieldParamName = $fieldParam;
                        }
                    }

                    $blockInstance->addField($fieldInstance);
                    $fieldTable->updateData(
                        [
                            'block' => $fieldInstance->getBlockId(),
                            'tablename' => $fieldInstance->table,
                            'presence' => $fieldInstance->presence,
                            'displaytype' => $fieldInstance->displaytype,
                            'sequence' => $fieldInstance->sequence,
                        ],
                        [
                            'fieldid' => $fieldInstance->id,
                        ],
                    );

                    if (!empty($picklistValues)) {
                        self::logSuccess('Picklist values create: ' . $fieldName);
                        self::logSuccess($picklistValues);

                        $picklistTable = 'vtiger_' . $fieldName;
                        $currentPicklistValues = [];

                        if (true === $fieldParams['picklist_overwrite']) {
                            $fieldInstance->deletePicklistValues();
                            $fieldInstance->setPicklistValues($picklistValues);
                        } else {
                            // Required current user disable for install
                            if ($currentUser->getId() && Vtiger_Utils::checkTable($picklistTable)) {
                                $currentPicklistValues = $fieldInstance->getPicklistValues();
                            }

                            foreach ($picklistValues as $picklistKey => $picklistValue) {
                                $picklistName = is_array($picklistValue) ? $picklistValue[0] : $picklistValue;

                                if (!isset($currentPicklistValues[$picklistName])) {
                                    $fieldInstance->setPicklistValues([$picklistKey => $picklistValue]);
                                }
                            }
                        }
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

                        $filterDynamicSequence++;
                        $filterSequence = !empty($fieldParams['filter_sequence']) ? $fieldParams['filter_sequence'] : $filterDynamicSequence;
                        $filterFields[$filterSequence] = $fieldInstance;
                    }

                    if (isset($fieldParams['entity_identifier'])) {
                        self::logSuccess('Entity Identifier: ' . $fieldName);

                        $entityIdentifiers[] = $fieldInstance;
                        $moduleInstance->setEntityIdentifier($entityIdentifiers);
                    }
                }
            }

            self::logSuccess('Filter start creating');

            if (!empty($filterFields)) {
                $filterInstance = $this->createFilter('All', $moduleInstance);

                ksort($filterFields);

                foreach ($filterFields as $filterSequence => $filterField) {
                    $filterInstance->addField($filterField, $filterSequence);
                }
            }

            self::logSuccess('Filter end creating');

            self::logSuccess('Link start creating');

            if (!empty($name) && !empty($parent)) {
                Settings_MenuEditor_Module_Model::addModuleToApp($name, $parent);

                self::logSuccess('Link created');
            } else {
                self::logError('Link not created');
            }
        }

        $this->install();

        Vtiger_Cache::delete('module', $moduleName);

        self::logSuccess('Module result: ' . $moduleName);
        self::logSuccess($moduleInstance);
    }

    public function createBlock($blockName, $moduleInstance)
    {
        $moduleName = $moduleInstance->name;

        if (!empty(self::$blocks[$blockName][$moduleName])) {
            return self::$blocks[$blockName][$moduleName];
        }

        $blockInstance = Vtiger_Block::getInstance($blockName, $moduleInstance);

        if (!$blockInstance) {
            $blockInstance = new Vtiger_Block();
        }

        $blockInstance->label = $blockName;

        $moduleInstance->addBlock($blockInstance);

        self::$blocks[$blockName][$moduleName] = $blockInstance;

        return $blockInstance;
    }

    public function createFilter($filterName, $moduleInstance)
    {
        $moduleName = $moduleInstance->name;

        if (!empty(self::$filters[$filterName][$moduleName])) {
            return self::$filters[$filterName][$moduleName];
        }

        self::logSuccess('Filter create: ' . $moduleName . ':' . $filterName);

        $filterInstance = Vtiger_Filter::getInstance($filterName, $moduleInstance);

        if (!$filterInstance) {
            $filterInstance = new Vtiger_Filter();
        }

        $filterInstance->name = $filterName;
        $filterInstance->isdefault = true;
        $filterInstance->save($moduleInstance);

        self::$filters[$filterName][$moduleName] = $filterInstance;

        return $filterInstance;
    }

    /**
     * @return void
     */
    abstract public function installTables(): void;

    /**
     * @param mixed $message
     * @return void
     */
    public static function logError($message): void
    {
        if (true !== vglobal('debug')) {
            return;
        }

        echo '<pre style="font-size: 20px; color: red;">' . print_r($message, true) . '</pre>';
    }

    /**
     * @param mixed $message
     * @return void
     */
    public static function logSuccess($message): void
    {
        if (true !== vglobal('debug')) {
            return;
        }

        echo '<pre style="font-size: 20px; color: darkolivegreen;">' . print_r($message, true) . '</pre>';
    }

    /**
     * @return mixed
     * @throws AppException
     */
    public function migrate()
    {
        throw new AppException('Migration undefined');
    }

    /**
     * @param $register
     * @return void
     */
    public function updateComments($register = true)
    {
        ModComments::removeWidgetFrom([$this->moduleName]);

        if ($register) {
            ModComments::addWidgetTo([$this->moduleName]);
        }
    }

    /**
     * @param bool $register
     * @return void
     */
    public function updateCron($register = true)
    {
        foreach ($this->registerCron as $cronInfo) {
            [$name, $handler, $frequency, $module, $sequence, $description] = array_pad($cronInfo, 6, null);

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }

    /**
     * @param $register
     * @return void
     */
    public function updateCustomLinks($register = true)
    {
        foreach ($this->registerCustomLinks as $customLink) {
            [$moduleName, $type, $label, $url, $icon, $sequence, $handler] = array_pad($customLink, 7, null);
            $module = Vtiger_Module::getInstance($moduleName);
            $url = str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $url);

            if ($module) {
                $module->deleteLink($type, $label);

                if ($register) {
                    $module->addLink($type, $label, $url, $icon, $sequence, $handler);
                }
            }
        }
    }

    /**
     * @param $register
     * @return void
     */
    public function updateEventHandler($register = true)
    {
        $eventsManager = new VTEventsManager($this->db);

        foreach ($this->registerEventHandler as $data) {
            [$events, $fileName, $className, $dependOn, $modules] = $data;

            $eventsManager->unregisterHandler($className);

            if ($register) {
                $dependOn = !empty($dependOn) ? $dependOn : '[]';

                foreach ((array)$events as $event) {
                    $eventsManager->registerHandler($event, $fileName, $className, $dependOn);

                    foreach ((array)$modules as $module) {
                        $eventsManager->setModuleForHandler($module, $className);
                    }
                }
            }
        }
    }

    /**
     * @param $register
     * @return void
     */
    public function updateHistory($register = true)
    {
        ModTracker::disableTrackingForModule(getTabid($this->moduleName));

        if ($register) {
            ModTracker::enableTrackingForModule(getTabid($this->moduleName));
        }
    }

    /**
     * @return void
     */
    protected function updateIcons()
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $from = sprintf('layouts/%s/modules/%s/%s.png', $layout, $this->moduleName, $this->moduleName);
        $to = sprintf('layouts/%s/skins/images/%s.png', $layout, $this->moduleName);

        if (is_file($from) && !is_file($to)) {
            copy($from, $to);
        }
    }



    /**
     * @return void
     */
    public function updateNumbering()
    {
        if (empty($this->moduleNumbering)) {
            return;
        }

        $focus = CRMEntity::getInstance($this->moduleName);
        $focus->setModuleSeqNumber('configure', $this->moduleName, $this->moduleNumbering, '0001');
        $focus->updateMissingSeqNumber($this->moduleName);
    }

    /**
     * @param $register
     * @return void
     */
    public function updateRelatedList($register = true)
    {
        foreach ($this->registerRelatedLists as $relatedList) {
            $module = Vtiger_Module::getInstance($relatedList[0]);
            $relatedModule = Vtiger_Module::getInstance($relatedList[1]);

            if ($module && $relatedModule) {
                $relatedLabel = isset($relatedList[2]) ? $relatedList[2] : $relatedModule->name;
                $relatedActions = isset($relatedList[3]) ? $relatedList[3] : '';
                $relatedFunction = isset($relatedList[4]) ? $relatedList[4] : 'get_related_list';
                $field = isset($relatedList[5]) ? Vtiger_Field_Model::getInstance($relatedList[5], $relatedModule) : '';
                $fieldId = $field ? $field->getId() : '';

                $module->unsetRelatedList($relatedModule, $relatedLabel);
                $module->unsetRelatedList($relatedModule, $relatedLabel, $relatedFunction);

                if ($register) {
                    $module->setRelatedList($relatedModule, $relatedLabel, $relatedActions, $relatedFunction, $fieldId);
                }
            }
        }
    }

    /**
     * @param bool $register
     * @return void
     * @throws Exception
     */
    public function updateSettingsLinks(bool $register = true): void
    {
        foreach ($this->registerSettingsLinks as $settingsLink) {
            [$name, $link, $block] = $settingsLink;

            $this->db->pquery('DELETE FROM vtiger_settings_field WHERE name=?', [$name]);

            if ($register) {
                $fieldId = $this->db->getUniqueID('vtiger_settings_field');
                $blockId = getSettingsBlockId($block);

                if (!$blockId) {
                    $blockId = $this->db->getUniqueID('vtiger_settings_blocks');
                    $sequenceResult = $this->db->pquery('SELECT max(sequence) AS max_seq FROM vtiger_settings_blocks');
                    $sequence = intval($this->db->query_result($sequenceResult, 0, 'max_seq')) + 1;
                    $this->db->pquery('INSERT INTO vtiger_settings_blocks(blockid, label, sequence) VALUES(?, ?, ?)', [$blockId, $block, $sequence]);
                }

                $sequenceResult = $this->db->pquery(
                    'SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid=?', [$blockId]
                );
                $sequence = intval($this->db->query_result($sequenceResult, 0, 'max_seq')) + 1;

                $this->db->pquery(
                    'INSERT INTO vtiger_settings_field(fieldid, blockid, name, linkto, sequence) VALUES (?,?,?,?,?)', [$fieldId, $blockId, $name, $link, $sequence]
                );
            }
        }
    }

    /**
     * @return void
     */
    public function updateToStandardModule()
    {
        // Mark the module as Standard module
        $this->db->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', [$this->moduleName]);
    }

    /**
     * @param $register
     * @return void
     */
    public function updateWorkflows($register = true)
    {
        foreach ($this->registerWorkflows as $registerWorkflow) {
            [$moduleName, $workflowName, $workflowLabel, $modules] = $registerWorkflow;

            $layout = Vtiger_Viewer::getLayoutName();

            if (!$register) {
                $this->db->pquery(
                    'DELETE FROM com_vtiger_workflow_tasktypes WHERE tasktypename=?', [$workflowName]
                );

                $this->db->pquery(
                    'DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ?', ['%:"' . $workflowName . '":%']
                );

                unlink(sprintf('modules/com_vtiger_workflow/tasks/%s.inc', $workflowName));
                unlink(sprintf('layouts/%s/modules/Settings/Workflows/Tasks/%s.tpl', $layout, $workflowName));
                continue;
            }

            $taskForm = sprintf('modules/%s/taskforms/%s.tpl', $moduleName, $workflowName);

            if (empty($modules)) {
                $modules = '{"include":[],"exclude":[]}';
            }

            $phpDestination = sprintf('modules/com_vtiger_workflow/tasks/%s.inc', $workflowName);
            $phpSource = sprintf('modules/%s/workflow/%s.inc', $moduleName, $workflowName);
            $phpFileExist = file_exists($phpDestination) || copy($phpSource, $phpDestination);

            $tplDestination = sprintf('layouts/%s/modules/Settings/Workflows/Tasks/%s.tpl', $layout, $workflowName);
            $tplSource = sprintf('layouts/%s/modules/%s/taskforms/%s.tpl', $layout, $moduleName, $workflowName);
            $tplFileExist = file_exists($tplDestination) || copy($tplSource, $tplDestination);

            if ($phpFileExist && $tplFileExist) {
                $result = $this->db->pquery(
                    'SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?', [$workflowName]
                );

                if (!$this->db->num_rows($result)) {
                    $workflowId = $this->db->getUniqueID('com_vtiger_workflow_tasktypes');
                    $values = [
                        'id' => $workflowId,
                        'tasktypename' => $workflowName,
                        'label' => $workflowLabel,
                        'classname' => $workflowName,
                        'classpath' => $phpSource,
                        'templatepath' => $taskForm,
                        'modules' => $modules,
                        'sourcemodule' => $moduleName,
                    ];
                    $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', 'com_vtiger_workflow_tasktypes', implode(',', array_keys($values)), generateQuestionMarks($values));
                    $this->db->pquery($sql, $values);
                }
            }
        }
    }

    /**
     * @param bool $register
     * @return void
     */
    public function updateAppointments(bool $register = true): void
    {
        $moduleName = $this->getModuleName();
        $integration = Settings_Appointments_Integration_Model::getInstance($moduleName);

        $integration->unsetField();
        $integration->unsetRelation();

        if ($register) {
            $integration->setField();
            $integration->setRelation();
        }
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @param bool $register
     * @return void
     */
    public function updateEmails(bool $register = true): void
    {
        $moduleName = $this->getModuleName();

        $emails = ITS4YouEmails_Integration_Model::getInstance($moduleName);
        $emails->updateRelation(false);
        $emails->updateLinks(false);
        $emails->unsetReferenceModule();

        if ($register) {
            $emails->updateRelation();
            $emails->updateLinks();
            $emails->setReferenceModule();
        }
    }
}