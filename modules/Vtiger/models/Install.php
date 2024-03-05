<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Vtiger_Install_Model extends Vtiger_Base_Model
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
    protected PearDatabase $db;
    protected string $eventType = '';
    protected string $moduleName = '';
    protected string $moduleNumbering = '';
    protected string $parentName = '';

    /**
     * @return void
     */
    abstract public function addCustomLinks(): void;

    /**
     * @param $column
     * @param $type
     * @return $this
     * @throws AppException
     */
    protected function createColumn($column, $type): self
    {
        if ($this->isEmpty('table')) {
            throw new AppException('Table is empty for create column');
        }

        if (!columnExists($column, $this->get('table'))) {
            $sql = sprintf('ALTER TABLE %s ADD %s %s', $this->get('table'), $column, $type);
        } else {
            $sql = sprintf('ALTER TABLE %s CHANGE %s %s %s', $this->get('table'), $column, $column, $type);
        }

        $this->db->query($sql);

        return $this;
    }

    public function createKey($criteria)
    {
        $this->db->pquery(
            sprintf(
                'ALTER TABLE %s ADD %s',
                $this->get('table'),
                $criteria
            )
        );

        return $this;
    }

    /**
     * @throws AppException
     */
    protected function createTable($firstColumn = '', $firstType = 'int(11)'): self
    {
        if ($this->isEmpty('table')) {
            throw new AppException('Table is empty for create table');
        }

        if (!empty($firstColumn)) {
            $criteria = sprintf('(%s %s)', $firstColumn, $firstType);
        }

        if (!$this->isEmpty('table_id')) {
            $criteria = sprintf(' (%s int(11) AUTO_INCREMENT,PRIMARY KEY (%s))', $this->get('table_id'), $this->get('table_id'));
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->get('table') . $criteria;

        $this->db->pquery($sql);

        return $this;
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
     * @param string $table
     * @param string|null $tableId
     * @return self
     */
    public function getTable(string $table, string|null $tableId): self
    {
        $clone = clone $this;
        $clone->db = PearDatabase::getInstance();
        $clone->set('table', $table);
        $clone->set('table_id', $tableId);

        return $clone;
    }

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
     * @return void
     * @throws AppException
     */
    public function installModule()
    {
        $this->installTables();

        self::logSuccess('Install tables');

        $moduleName = $this->moduleName;
        $moduleFocus = CRMEntity::getInstance($moduleName);

        $entity = in_array(get_parent_class($moduleFocus), ['CRMEntity', 'Vtiger_CRMEntity']);
        $baseTableId = $moduleFocus->table_index;
        $baseTable = $moduleFocus->table_name;
        $label = $moduleFocus->moduleLabel;
        $name = $moduleFocus->moduleName;
        $parent = $moduleFocus->parentName;
        $cfTable = $moduleFocus->customFieldTable[0];

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

            $filterSequence = 0;
            $filter = Vtiger_Filter::getInstance('All', $moduleInstance);

            if (!$filter) {
                $filter = new Vtiger_Filter();
            }

            $filter->name = 'All';
            $filter->isdefault = true;
            $filter->save($moduleInstance);

            if (isset($blocks['LBL_ITEM_DETAILS'])) {
                $taxResult = $this->db->pquery('SELECT * FROM vtiger_inventorytaxinfo');

                while ($row = $this->db->fetchByAssoc($taxResult)) {
                    $blocks['LBL_ITEM_DETAILS'][$row['taxname']] = [
                        'table' => 'vtiger_inventoryproductrel',
                        'label' => $row['taxlabel'],
                        'uitype' => 83,
                        'typeofdata' => 'V~O',
                        'displaytype' => 5,
                        'masseditable' => 0,
                    ];
                }
            }

            foreach ($blocks as $block => $fields) {
                self::logSuccess('Block create: ' . $block);

                $blockInstance = Vtiger_Block::getInstance($block, $moduleInstance);

                if (!$blockInstance) {
                    $blockInstance = new Vtiger_Block();
                }

                $blockInstance->label = $block;

                $moduleInstance->addBlock($blockInstance);

                foreach ($fields as $fieldName => $fieldParams) {
                    if (empty($fieldName)) {
                        continue;
                    }

                    self::logSuccess('Field create: ' . $fieldName);

                    $relatedModules = [];
                    $picklistValues = [];

                    $fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleInstance);

                    if (!$fieldInstance) {
                        $fieldInstance = new Vtiger_Field();
                    }

                    $fieldInstance->name = $fieldName;
                    $fieldInstance->column = $fieldName;
                    $fieldInstance->table = $baseTable;

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

                    $params = [
                        'block' => $fieldInstance->getBlockId(),
                        'presence' => $fieldInstance->presence,
                        'displaytype' => $fieldInstance->displaytype,
                    ];
                    $sql = sprintf('UPDATE vtiger_field SET %s=? WHERE fieldid=?', implode('=?,', array_keys($params)));
                    $this->db->pquery($sql, [$params, $fieldInstance->id]);

                    if (!empty($picklistValues)) {
                        self::logSuccess('Picklist values create: ' . $fieldName);
                        self::logSuccess($picklistValues);

                        $picklistTable = 'vtiger_' . $fieldName;
                        $currentPicklistValues = [];

                        if (Vtiger_Utils::checkTable($picklistTable)) {
                            $currentPicklistValues = $fieldInstance->getPicklistValues();
                        }

                        foreach ($picklistValues as $picklistKey => $picklistValue) {
                            $picklistName = is_array($picklistValue) ? $picklistValue[0] : $picklistValue;

                            if (!isset($currentPicklistValues[$picklistName])) {
                                $fieldInstance->setPicklistValues([$picklistKey => $picklistValue]);
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

                        $filterSequence++;
                        $filter->addField($fieldInstance, $filterSequence);
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
     * @param bool $register
     * @return void
     */
    public function updateCron($register = true)
    {
        $this->db->pquery('ALTER TABLE vtiger_cron_task MODIFY COLUMN id INT auto_increment ');

        foreach ($this->registerCron as $cronInfo) {
            [$name, $handler, $frequency, $module, $sequence, $description] = array_pad($cronInfo, 6, null);

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }

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

    public function updateEventHandler($register = true)
    {
        $eventsManager = new VTEventsManager($this->db);

        foreach ($this->registerEventHandler as $data) {
            [$events, $fileName, $className, $condition, $dependOn, $modules] = $data;

            $eventsManager->unregisterHandler($className);

            if ($register) {
                $condition = !empty($condition) ? $condition : '';
                $dependOn = !empty($dependOn) ? $dependOn : '[]';

                foreach ((array)$events as $event) {
                    $eventsManager->registerHandler($event, $fileName, $className, $condition, $dependOn);

                    foreach ((array)$modules as $module) {
                        $eventsManager->setModuleForHandler($module, $className);
                    }
                }
            }
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

    public function updateSettingsLinks($register = true)
    {
        foreach ($this->registerSettingsLinks as $settingsLink) {
            [$name, $link, $block] = $settingsLink;

            $this->db->pquery('DELETE FROM vtiger_settings_field WHERE name=?', array($name));

            if ($register) {
                $fieldId = $this->db->getUniqueID('vtiger_settings_field');
                $blockId = getSettingsBlockId($block) ?: getSettingsBlockId('LBL_EXTENSIONS');

                $sequenceResult = $this->db->pquery(
                    'SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid=?', array($blockId)
                );
                $sequence = intval($this->db->query_result($sequenceResult, 0, 'max_seq')) + 1;

                $this->db->pquery(
                    'INSERT INTO vtiger_settings_field(fieldid, blockid, name, linkto, sequence) VALUES (?,?,?,?,?)', array($fieldId, $blockId, $name, $link, $sequence)
                );
            }
        }
    }

    public function updateWorkflows($register = true)
    {
        foreach ($this->registerWorkflows as $registerWorkflow) {
            [$moduleName, $workflowName, $workflowLabel, $modules] = $registerWorkflow;

            $layout = Vtiger_Viewer::getLayoutName();

            if (!$register) {
                $this->db->pquery(
                    'DELETE FROM com_vtiger_workflow_tasktypes WHERE tasktypename=?', array($workflowName)
                );

                $this->db->pquery(
                    'DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ?', array('%:"' . $workflowName . '":%')
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
                    'SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?', array($workflowName)
                );

                if (!$this->db->num_rows($result)) {
                    $workflowId = $this->db->getUniqueID('com_vtiger_workflow_tasktypes');
                    $values = array(
                        'id' => $workflowId,
                        'tasktypename' => $workflowName,
                        'label' => $workflowLabel,
                        'classname' => $workflowName,
                        'classpath' => $phpSource,
                        'templatepath' => $taskForm,
                        'modules' => $modules,
                        'sourcemodule' => $moduleName,
                    );
                    $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', 'com_vtiger_workflow_tasktypes', implode(',', array_keys($values)), generateQuestionMarks($values));
                    $this->db->pquery($sql, $values);
                }
            }
        }
    }

    public function updateNumbering()
    {
        if (empty($this->moduleNumbering)) {
            return;
        }

        $focus = CRMEntity::getInstance($this->moduleName);
        $focus->setModuleSeqNumber('configure', $this->moduleName, $this->moduleNumbering, '0001');
        $focus->updateMissingSeqNumber($this->moduleName);
    }
}