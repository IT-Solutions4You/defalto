<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_Integration_Model extends Vtiger_Base_Model
{
    public $moduleName = '';

    /**
     * @param string $module
     * @return bool
     */
    public static function isActive($module)
    {
        $instance = self::getInstance($module);

        return !empty($instance->getRelationModel()) && in_array($module, $instance->getReferenceModules());
    }

    /**
     * @param string $module
     * @return bool
     */
    public static function isReferenceActive($module)
    {
        $instance = self::getInstance($module);

        return in_array($module, $instance->getReferenceModules());
    }

    /**
     *
     * @return self
     */
    public static function getInstance($module)
    {
        $self = new self();
        $self->moduleName = $module;

        return $self;
    }

    public static function getSupportedModules() {
        $supportedModule = Vtiger_Module_Model::getEntityModules();
        $unsetModules = [
            'SMSNotifier', 'PBXManager', 'ModComments', 'ITS4YouEmails',
        ];

        foreach ($unsetModules as $unsetModule) {
            unset($supportedModule[getTabid($unsetModule)]);
        }

        return $supportedModule;
    }

    public function getRelationModel()
    {
        $parentModuleModel = $this->getModuleModel();
        $relatedModuleModel = $this->getEmailsModel();

        return Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel);
    }

    public function getModuleModel()
    {
        return Vtiger_Module_Model::getInstance($this->moduleName);
    }

    public function getEmailsModel()
    {
        return Vtiger_Module_Model::getInstance('ITS4YouEmails');
    }

    public function setReferenceModule()
    {
        $this->getReferenceField()->setRelatedModules([$this->moduleName]);
    }

    public function unsetReferenceModule()
    {
        $this->getReferenceField()->unsetRelatedModules([$this->moduleName]);
    }

    public function getReferenceField()
    {
        return Vtiger_Field_Model::getInstance('related_to', $this->getEmailsModel());
    }

    public function getReferenceModules()
    {
        return $this->getReferenceField()->getReferenceList();
    }

    public function updateRelation($register = true)
    {
        $relatedModule = $this->getEmailsModel();
        $relatedFunction = 'get_related_list';
        $relatedActions = 'SELECT';

        /** @var Vtiger_Module_Model $module */
        $module = $this->getModuleModel();
        $module->unsetRelatedList($relatedModule, '', $relatedFunction);

        if($register) {
            $module->setRelatedList($relatedModule, '', $relatedActions, $relatedFunction);
        }
    }
}