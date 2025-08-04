<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_ModuleRequirements_Model extends Vtiger_Base_Model
{
    public array $cron = [];
    public array $customLinks = [];
    /**
     * @var PearDatabase
     */
    public PearDatabase $db;
    public array $eventHandler = [];
    public $installModel = null;
    /**
     * @var string
     */
    public string $moduleName = '';
    public array $relatedList = [];

    /**
     * @throws Exception
     */
    public function getCron()
    {
        $info = [];

        foreach ($this->cron as $cron) {
            [$name, $handler, $frequency, $module, $sequence, $description] = $cron;

            $data = [
                'name'      => $name,
                'module'    => $module,
                'frequency' => $frequency,
                'handler'   => $handler,
            ];

            $this->validateCron($data);

            $info[] = $data;
        }

        return $info;
    }

    public function getCustomLinks()
    {
        $info = [];

        foreach ($this->customLinks as $customLink) {
            [$moduleName, $type, $label, $url, $icon, $sequence, $handlerInfo] = $customLink;

            $data = [
                'module' => $moduleName,
                'type'   => $type,
                'label'  => $label,
                'url'    => str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $url),
            ];

            $this->validateCustomLink($data);

            $info[] = $data;
        }

        return $info;
    }

    public function getDataFromFunction($value)
    {
        if (method_exists($this, $value)) {
            return $this->$value();
        }

        $install = $this->getInstallModel();

        if (method_exists($install, $value)) {
            return $install->$value();
        }

        return [];
    }

    /**
     * @return string
     */
    public function getDefaultUrl()
    {
        return 'index.php?module=Installer&view=Requirements&mode=Module&sourceModule=' . $this->getModuleName();
    }

    public function getEventHandler(): array
    {
        $info = [];

        foreach ($this->eventHandler as $handler) {
            [$events, $fileName, $className, $condition, $dependOn, $modules] = $handler;

            foreach ((array)$events as $eventName) {
                $modules = !empty($modules) ? $modules : [''];

                foreach ((array)$modules as $moduleName) {
                    $data = [
                        'event_name' => $eventName,
                        'module'     => $moduleName,
                        'file_name'  => $fileName,
                        'class_name' => $className,
                    ];

                    $this->validateEventHandler($data);

                    $info[] = $data;
                }
            }
        }

        return $info;
    }

    public function getHeaders($type)
    {
        switch ($type) {
            case 'links':
                return [
                    'LBL_MODULE'   => 'module',
                    'LBL_LABEL'    => 'label',
                    'LBL_TYPE'     => 'type',
                    'LBL_LINK_URL' => 'url',
                ];
            case 'cron':
                return [
                    'LBL_MODULE'       => 'module',
                    'LBL_NAME'         => 'name',
                    'LBL_FREQUENCY'    => 'frequency',
                    'LBL_HANDLER_FILE' => 'handler',
                ];
            case 'handler':
                return [
                    'LBL_MODULE'     => 'module',
                    'LBL_EVENT_NAME' => 'event_name',
                    'LBL_CLASS'      => 'class_name',
                    'LBL_EVENT_FILE' => 'file_name',
                ];
            case 'related_list':
                return [
                    'LBL_MODULE'         => 'module',
                    'LBL_RELATED_MODULE' => 'related_module',
                    'LBL_RELATED_LABEL'  => 'related_label',
                    'LBL_FUNCTION'       => 'function',
                ];
        }

        $install = $this->getInstallModel();

        if (method_exists($install, 'getRequirementHeaders')) {
            return $install->getRequirementHeaders($type);
        }

        return [];
    }

    public function getInstallModel(): null|Core_Install_Model
    {
        if (null === $this->installModel) {
            $this->installModel = Core_Install_Model::getInstance('requirements', $this->getModuleName());
        }

        return $this->installModel;
    }

    /**
     * @param $moduleName
     *
     * @return self
     */
    public static function getInstance($moduleName): self
    {
        $self = new self();
        $self->db = PearDatabase::getInstance();
        $self->setModuleName($moduleName);

        return $self;
    }

    /**
     * @return string
     */
    public function getModuleLabel(): string
    {
        return vtranslate($this->getModuleName(), $this->getModuleName());
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @param string $value
     */
    public function setModuleName(string $value): void
    {
        $this->moduleName = $value;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRelatedList(): array
    {
        $info = [];

        foreach ($this->relatedList as $value) {
            [$moduleName, $relationModule, $relationLabel, $actions, $function] = $value;

            $data = [
                'module'         => $moduleName,
                'related_module' => $relationModule,
                'related_label'  => !empty($relationLabel) ? $relationLabel : $relationModule,
                'actions'        => $actions,
                'function'       => !empty($function) ? $function : 'get_related_list',
            ];
            $this->validateRelatedList($data);

            $info[] = $data;
        }

        return $info;
    }

    /**
     * @return array
     */
    public static function getSourceModules(): array
    {
        $adb = PearDatabase::getInstance();
        $modules = [];
        $result = $adb->pquery('SELECT * FROM vtiger_tab');

        while ($row = $adb->fetchByAssoc($result)) {
            $class = $row['name'] . '_Install_Model';

            if (class_exists($class)) {
                $modules[$row['tabid']] = self::getInstance($row['name']);
            }
        }

        return $modules;
    }

    public function getValidations()
    {
        $defaultValidation = [
            [
                'type'     => 'links',
                'label'    => 'LBL_CUSTOM_LINKS',
                'function' => 'getCustomLinks',
            ],
            [
                'type'     => 'cron',
                'label'    => 'LBL_CRON',
                'function' => 'getCron',
            ],
            [
                'type'     => 'handler',
                'label'    => 'LBL_EVENT_HANDLER',
                'function' => 'getEventHandler',
            ],
            [
                'type'     => 'related_list',
                'label'    => 'LBL_RELATED_LIST',
                'function' => 'getRelatedList',
            ],
        ];
        $validations = [];
        $install = $this->getInstallModel();

        if ($install && method_exists($install, 'getRequirementValidations')) {
            $validations = $install->getRequirementValidations();
        }

        return array_merge($defaultValidation, $validations);
    }

    public function retrieveCron(): void
    {
        $install = $this->getInstallModel();

        if ($install && method_exists($install, 'retrieveCron')) {
            $install->retrieveCron();
        }

        if (isset($install->registerCron)) {
            $this->cron = $install->registerCron;
        }
    }

    public function retrieveCustomLinks(): void
    {
        $install = $this->getInstallModel();

        if (method_exists($install, 'retrieveCustomLinks')) {
            $install->retrieveCustomLinks();
        }

        if (isset($install->registerCustomLinks)) {
            $this->customLinks = $install->registerCustomLinks;
        }
    }

    public function retrieveData(): void
    {
        $this->retrieveCustomLinks();
        $this->retrieveEventHandler();
        $this->retrieveRelatedList();
        $this->retrieveCron();
    }

    public function retrieveEventHandler(): void
    {
        $install = $this->getInstallModel();

        if (method_exists($install, 'retrieveEventHandler')) {
            $install->retrieveEventHandler();
        }

        if (isset($install->registerEventHandler)) {
            $this->eventHandler = $install->registerEventHandler;
        }
    }

    public function retrieveRelatedList(): void
    {
        $install = $this->getInstallModel();

        if (method_exists($install, 'retrieveRelatedList')) {
            $install->retrieveRelatedList();
        }

        if (isset($install->registerRelatedLists)) {
            $this->relatedList = $install->registerRelatedLists;
        }
    }

    /**
     * @throws Exception
     */
    public function validateCron(array &$data): void
    {
        $result = $this->db->pquery(
            'SELECT * FROM vtiger_cron_task WHERE name=? AND handler_file=? AND module=?',
            [$data['name'], $data['handler'], $data['module']],
        );
        $number = $this->db->num_rows($result);
        $row = $this->db->query_result_rowdata($result);

        $validate = 1 === $number;
        $message = 1 < $number ? 'LBL_DUPLICATE_LINKS' : '';

        if (empty($row['status'])) {
            $message = 'LBL_CRON_DISABLED';
            $validate = false;
        }

        if (intval($data['frequency']) !== intval($row['frequency'])) {
            $message = 'LBL_DIFFERENT_FREQUENCY';
        }

        $data['validate'] = $validate;
        $data['validate_message'] = $message;
    }

    public function validateCustomLink(array &$data): void
    {
        $result = $this->db->pquery(
            'SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? AND linkurl=?',
            [getTabid($data['module']), $data['type'], $data['url']],
        );
        $number = $this->db->num_rows($result);

        $data['validate'] = 1 === $number;
        $data['validate_message'] = 1 < $number ? 'LBL_DUPLICATE_LINKS' : '';
    }

    public function validateEventHandler(array &$data): void
    {
        $sql = 'SELECT * FROM vtiger_eventhandlers 
            LEFT JOIN vtiger_eventhandler_module ON vtiger_eventhandler_module.handler_class=vtiger_eventhandlers.handler_class
            WHERE vtiger_eventhandlers.handler_class=? AND handler_path=? AND event_name=?';
        $params = [$data['class_name'], $data['file_name'], $data['event_name']];

        if (!empty($data['module'])) {
            $sql .= ' AND module_name=? ';
            array_push($params, $data['module']);
        }

        $result = $this->db->pquery($sql, $params);
        $number = $this->db->num_rows($result);

        $data['validate'] = 1 === $number;
        $data['validate_message'] = 1 < $number ? 'LBL_DUPLICATE_LINKS' : '';
    }

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function validateRelatedList(array &$data): void
    {
        $sql = 'SELECT * FROM vtiger_relatedlists 
            WHERE tabid=? AND related_tabid=? AND label=? AND name=?';
        $params = [getTabid($data['module']), getTabid($data['related_module']), $data['related_label'], $data['function']];

        $result = $this->db->pquery($sql, $params);
        $row = $this->db->query_result_rowdata($result);
        $number = $this->db->num_rows($result);
        $message = 1 < $number ? 'LBL_DUPLICATE_RELATED_LISTS' : '';

        if ($data['actions'] !== $row['actions']) {
            $message = 'LBL_DIFFERENT_ACTIONS';
        }

        $data['validate'] = 1 === $number;
        $data['validate_message'] = $message;
    }
}