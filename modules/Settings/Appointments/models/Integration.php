<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Appointments_Integration_Model extends Vtiger_Base_Model
{
    /**
     * @var array
     */
    public static array $disabledModules = [
        'Appointments',
        'PBXManager',
        'SMSNotifier',
        'ModComments',
    ];

    public static array $disabledFieldModules = [
        'Accounts',
        'Contacts',
    ];

    /**
     * @var string
     */
    public string $moduleName;
    /**
     * @var Vtiger_Module_Model
     */
    public Vtiger_Module_Model $moduleModel;
    /**
     * @var Vtiger_Module_Model
     */
    public Vtiger_Module_Model $fieldModule;
    /**
     * @var Vtiger_Field_Model
     */
    public Vtiger_Field_Model $fieldModel;
    /**
     * @var Vtiger_Relation_Model
     */
    public Vtiger_Relation_Model $relationModel;
    /**
     * @var array
     */
    public array $referenceModules;

    /**
     * @return array
     */
    public static function getModules(): array
    {
        $modules = Vtiger_Module_Model::getEntityModules();
        $instances = [];

        foreach ($modules as $moduleModel) {
            $instance = self::getInstance($moduleModel);

            if ($instance) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    /**
     * @param $module
     *
     * @return false|Settings_Appointments_Integration_Model
     */
    public static function getInstance($module)
    {
        $instance = new self();

        if (!is_object($module)) {
            $module = Vtiger_Module_Model::getInstance($module);
        }

        $instance->moduleName = $module->getName();
        $instance->moduleModel = $module;

        if ($instance->isDisabledModule()) {
            return false;
        }

        $instance->retrieveField();
        $instance->retrieveRelation();

        return $instance;
    }

    /**
     * @return bool
     */
    public function isDisabledModule(): bool
    {
        return in_array($this->moduleName, self::$disabledModules);
    }

    public function getFieldName(): string
    {
        return match ($this->moduleName) {
            'Accounts' => 'account_id',
            'Contacts' => 'contact_id',
            default => 'parent_id',
        };
    }

    /**
     * @return void
     */
    public function retrieveField()
    {
        $this->fieldModule = Vtiger_Module_Model::getInstance('Appointments');
        $this->fieldModel = Vtiger_Field_Model::getInstance($this->getFieldName(), $this->fieldModule);
        $this->referenceModules = $this->fieldModel->getReferenceList();
    }

    /**
     * @return void
     */
    public function retrieveRelation()
    {
        $relation = Vtiger_Relation_Model::getInstance($this->moduleModel, $this->fieldModule);

        if ($relation) {
            $this->relationModel = $relation;
        }
    }

    /**
     * @return bool
     */
    public function isActiveField(): bool
    {
        return $this->isActiveReferenceModule() && $this->isActiveRelation();
    }

    /**
     * @return bool
     */
    public function isActiveReferenceModule(): bool
    {
        return in_array($this->moduleName, array_merge($this->referenceModules, self::$disabledFieldModules));
    }

    /**
     * @return bool
     */
    public function isActiveRelation(): bool
    {
        return !empty($this->relationModel);
    }

    /**
     * @return void
     */
    public function setRelation()
    {
        $this->moduleModel->setRelatedList($this->fieldModule, 'Appointments', '', 'get_related_list', $this->fieldModel->getId());
    }

    /**
     * @return void
     */
    public function setField()
    {
        if (in_array($this->moduleName, self::$disabledFieldModules)) {
            return;
        }

        $this->fieldModel->setRelatedModules([$this->moduleName]);
    }

    public function unsetField()
    {
        $this->fieldModel->unsetRelatedModules([$this->moduleName]);
    }

    public function unsetRelation()
    {
        $this->moduleModel->unsetRelatedList($this->fieldModule);
    }

    /**
     * @return void
     */
    public function isActiveWidget()
    {
    }
}