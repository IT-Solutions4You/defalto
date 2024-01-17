<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Vtiger_Kanban_Model extends Vtiger_Base_Model
{
    /**
     * @var Vtiger_Field_Model
     */
    public Vtiger_Field_Model $fieldModel;
    /**
     * @var string
     */
    public string $fieldName = '';
    public int $kanbanId = 0;
    /**
     * @var Vtiger_Module_Model
     */
    public Vtiger_Module_Model $moduleModel;
    /**
     * @var string
     */
    public string $moduleName = '';
    /**
     * @var array
     */
    public array $picklistValues = [];
    /**
     * @var array|mixed|String
     */
    protected $cvId;
    /**
     * @var int
     */
    protected int $cvLimit = 5;
    /**
     * @var int
     */
    protected int $cvPage = 1;
    /**
     * @var string
     */
    protected string $defaultColor = '#5E81F4';
    /**
     * @var array|string[]
     */
    protected array $defaultFields = [
        'Potentials' => 'sales_stage',
    ];
    /**
     * @var Vtiger_ListView_Model
     */
    protected Vtiger_ListView_Model $listViewModel;
    /**
     * @var Vtiger_Paging_Model
     */
    protected Vtiger_Paging_Model $pagingModel;
    /**
     * @var object
     */
    protected object $queryGenerator;

    /**
     * @param $values
     * @return void
     */
    public function filterFieldValues($values)
    {
        $this->picklistValues = array_intersect($this->picklistValues, $values);
    }

    /**
     * @param $value
     * @return void
     */
    public function filterRecordsByAssignUsers($value)
    {
        $queryGenerator = $this->getQueryGenerator();
        $queryGenerator->addCondition('assigned_user_id', $value, 'c', 'AND');
    }

    /**
     * @param $value
     * @return void
     */
    public function filterRecordsByFieldValue($value)
    {
        $queryGeneratorClone = clone $this->getQueryGenerator();
        $queryGeneratorClone->addUserSearchConditions(array('search_field' => $this->getFieldName(), 'search_text' => $value, 'operator' => 'e'));

        $this->getListView()->set('query_generator', $queryGeneratorClone);
    }

    /**
     * @param $userId
     * @return mixed|string
     */
    public function getAssignedImage($userId)
    {
        $groupModel = Settings_Groups_Record_Model::getInstance($userId);

        if ($groupModel) {
            $imageUrl = 'Group';
        } else {
            $recordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');

            if ($recordModel) {
                foreach ($recordModel->getImageDetails() as $image) {
                    if (!isset($image['url'])) {
                        $image['url'] = $image['path'] . '_' . $image['name'];
                    }

                    if (!empty($image['name'])) {
                        $imageUrl = $image['url'];
                        break;
                    }
                }
            }
        }

        if (empty($imageUrl)) {
            $imageUrl = 'User';
        }

        return $imageUrl;
    }

    /**
     * @return array
     */
    public function getAssignedUsers(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return [
            'users' => $currentUser->getAccessibleUsersForModule($this->getModuleName()),
            'groups' => $currentUser->getAccessibleGroupForModule($this->getModuleName()),
        ];
    }

    /**
     * @return array
     */
    public function getCustomViewFilters(): array
    {
        return CustomView_Record_Model::getAllByGroup($this->getModuleName());
    }

    /**
     * @return array|mixed|String
     */
    public function getCustomViewId()
    {
        return $this->cvId;
    }

    /**
     * @return int
     */
    public function getCustomViewLimit(): int
    {
        return $this->cvLimit;
    }

    /**
     * @return int
     */
    public function getCustomViewPage(): int
    {
        return $this->cvPage;
    }

    /**
     * @return string
     */
    public function getDefaultColor(): string
    {
        return $this->defaultColor;
    }

    /**
     * @return Vtiger_Field_Model
     */
    public function getFieldModel(): Vtiger_Field_Model
    {
        return $this->fieldModel;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return array
     */
    public function getFieldValues(): array
    {
        return $this->picklistValues;
    }

    /**
     * @return array
     */
    public function getFieldValuesColor(): array
    {
        $fieldValues = $this->getFieldValues();
        $colors = [];
        $fieldName = $this->getFieldName();
        foreach ($fieldValues as $fieldValue) {
            $color = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $fieldValue);

            if (!is_null($color) && $color !== '#ffffff') {
                $colors[$fieldValue] = $color;
            } else {
                $colors[$fieldValue] = $this->getDefaultColor();
            }
        }

        return $colors;
    }

    /**
     * @param $moduleName
     * @return self
     * @throws AppException
     */
    public static function getInstance($moduleName): self
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Kanban', $moduleName);
        /** @var self $instance */
        $instance = new $modelClassName();
        $instance->setModuleName($moduleName);
        $instance->retrieveModuleModel();
        $instance->retrieveDefaultField();

        return $instance;
    }

    public function getKanbanId(): int
    {
        return $this->kanbanId;
    }

    /**
     * @return string
     */
    public function getListLink(): string
    {
        return $this->getModuleModel()->getListViewUrl();
    }

    /**
     * @return object
     */
    public function getListView()
    {
        return $this->listViewModel;
    }

    /**
     * @return Vtiger_Module_Model
     */
    public function getModuleModel(): Vtiger_Module_Model
    {
        return $this->moduleModel;
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @return string
     */
    public function getNewRecordLink(): string
    {
        $moduleName = $this->getModuleName();
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

        if (isPermitted($moduleName, 'EditView')) {
            return $recordModel->getEditViewUrl();
        }

        return '';
    }

    /**
     * @return Vtiger_Paging_Model
     */
    public function getPaging(): Vtiger_Paging_Model
    {
        return $this->pagingModel;
    }

    /**
     * @return object
     */
    public function getQueryGenerator(): object
    {
        return $this->queryGenerator;
    }

    public static function getRGBFromHex($value): string
    {
        return implode(',', sscanf($value, "#%02x%02x%02x"));
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @return array
     */
    public function getRecordInfo(Vtiger_Record_Model $recordModel): array
    {
        $recordId = $recordModel->getId();
        $headerValues = [];

        $fieldModels = $this->getModuleModel()->getHeaderViewFieldsList();

        /**
         * @var Vtiger_Field_Model $fieldModel
         */
        foreach ($fieldModels as $fieldModel) {
            $headerField = $fieldModel->getName();
            $headerValues[$headerField] = array(
                'label' => vtranslate($fieldModel->get('label'), $this->getModuleName()),
                'value' => $recordModel->get($headerField),
                'display_value' => $recordModel->getDisplayValue($headerField),
            );
        }

        return [
            'id' => $recordId,
            'name' => $recordModel->getName(),
            'data' => $recordModel->getData(),
            'headers' => $headerValues,
            'edit_url' => $recordModel->getEditViewUrl(),
            'detail_url' => '#' . $recordId,
            'image' => $this->getAssignedImage($recordModel->get('assigned_user_id')),
        ];
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        $records = array();

        foreach ($this->getListView()->getListViewEntries($this->getPaging()) as $recordModel) {
            $records[] = Vtiger_Record_Model::getInstanceById($recordModel->getId(), $recordModel->getModuleName());
        }

        return $records;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getRecordsCount(): int
    {
        $adb = PearDatabase::getInstance();
        $moduleModel = $this->getListView()->getModule();

        $listQuery = preg_split('/ from /im', $this->getListView()->getQuery());
        $listQuery[0] = 'SELECT count(distinct(' . $moduleModel->get('basetable') . '.' . $moduleModel->get('basetableid') . ')) AS count ';
        $listQuery = implode(' FROM ', $listQuery);

        $result = $adb->pquery($listQuery);

        return (int)$adb->query_result($result, 0, 'count');
    }

    public function getRecordsHeader()
    {
        $function = 'getRecordsHeaderFor' . $this->getModuleName();

        if (method_exists($this, $function)) {
            return $this->$function();
        }

        return [];
    }

    public function getRecordsHeaderForPotentials(): array
    {
        $adb = PearDatabase::getInstance();

        $listQuery = preg_split('/ from /im', $this->getListView()->getQuery());
        $listQuery[0] = 'SELECT sum(vtiger_potential.amount) AS amount ';
        $listQuery = implode(' FROM ', $listQuery);

        $result = $adb->pquery($listQuery);
        $sum = $adb->query_result($result, 0, 'amount');
        $moduleName = $this->getModuleName();

        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $recordModel->set('amount', $sum);

        return [
            'amount' => [
                'label' => vtranslate('Amount', $moduleName),
                'value' => $sum,
                'display_value' => $recordModel->getDisplayValue('amount'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getRecordsInfo(): array
    {
        $records = $this->getRecords();
        $recordsInfo = [];

        foreach ($records as $recordModel) {
            $recordsInfo[] = $this->getRecordInfo($recordModel);
        }

        return $recordsInfo;
    }

    /**
     * @return array
     */
    public function getRolePicklistValues(): array
    {
        return getPickListValues($this->getFieldName(), Users_Record_Model::getCurrentUserModel()->getRole());
    }

    /**
     * @return void
     */
    public function retrieveCustomViewId()
    {
        if (empty($this->cvId)) {
            $customView = new CustomView();
            $this->cvId = (int)$customView->getViewIdByName('All', $this->getModuleName());
        }
    }

    /**
     * @return void
     */
    public function retrieveDefaultField()
    {
        $moduleName = $this->getModuleName();

        if (isset($this->defaultFields[$moduleName])) {
            $this->setFieldName($this->defaultFields[$moduleName]);
        }
    }

    /**
     * @return void
     */
    public function retrieveFieldInfo()
    {
        $this->retrieveFieldModel();
        $this->retrieveFieldValues();
    }

    /**
     * @return void
     */
    public function retrieveFieldModel()
    {
        $this->fieldModel = Vtiger_Field_Model::getInstance($this->getFieldName(), $this->getModuleModel());
    }

    /**
     * @return void
     */
    public function retrieveFieldValues()
    {
        $field = $this->getFieldModel();
        $this->picklistValues = $field ? array_keys($field->getPicklistValues()) : [];

        if ($field->isRoleBased()) {
            $this->filterFieldValues($this->getRolePicklistValues());
        }
    }

    public function retrieveKanbanInfo()
    {
        $class = 'ITS4YouKanbanView_Record_Model';
        $function = 'retrieveKanbanInfo';

        if (class_exists($class) && method_exists($class, $function)) {
            $class::$function($this);
        } else {
            throw new AppException(vtranslate('LBL_REQUIRE_KANBAN_PRO', $this->getModuleName()));
        }
    }

    /**
     * @return void
     */
    public function retrieveModuleModel()
    {
        $this->moduleModel = Vtiger_Module_Model::getInstance($this->getModuleName());
    }

    /**
     * @return void
     */
    public function retrieveRecordsInfo()
    {
        $this->retrieveCustomViewId();
        $cvId = $this->getCustomViewId();
        $moduleName = $this->getModuleName();

        $this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
        $this->pagingModel = new Vtiger_Paging_Model();
        $this->pagingModel->set('limit', $this->getCustomViewLimit());
        $this->pagingModel->set('page', $this->getCustomViewPage());
        $this->setQueryGenerator($this->getListView()->get('query_generator'));
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     */
    public function retrieveRequestInfo(Vtiger_Request $request)
    {
        if (!$request->isEmpty('field')) {
            $this->setFieldName($request->get('field'));
        }

        if (!$request->isEmpty('view_page')) {
            $this->setCustomViewPage((int)$request->get('view_page'));
        }

        if (!$request->isEmpty('view_name')) {
            $this->setCustomViewId((int)$request->get('view_name'));
        }

        if (!$request->isEmpty('record')) {
            $this->setKanbanId((int)$request->get('record'));
            $this->retrieveKanbanInfo();
        } else {
            $this->retrieveFieldInfo();
        }

        $this->retrieveRecordsInfo();

        if (!$request->isEmpty('field_values')) {
            $requestFieldValues = explode(',', $request->get('field_values'));
            $this->filterFieldValues($requestFieldValues);
        }

        if (!$request->isEmpty('assigned_user')) {
            $this->filterRecordsByAssignUsers($request->get('assigned_user'));
        }
    }

    /**
     * @param int $value
     * @return void
     */
    public function setCustomViewId(int $value)
    {
        $this->cvId = $value;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setCustomViewLimit(int $value)
    {
        $this->cvLimit = $value;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setCustomViewPage(int $value)
    {
        $this->cvPage = $value;
    }

    /**
     * @param $name
     * @return void
     */
    public function setFieldName($name)
    {
        $this->fieldName = $name;
    }

    public function setKanbanId(int $value)
    {
        $this->kanbanId = $value;
    }

    /**
     * @param $name
     * @return void
     */
    public function setModuleName($name)
    {
        $this->moduleName = $name;
    }

    /**
     * @param object $value
     */
    public function setQueryGenerator(object $value)
    {
        $this->queryGenerator = $value;
    }
}