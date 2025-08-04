<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Vtiger_ModuleMeta_Model extends Vtiger_Base_Model
{
    var $moduleName = false;

    var $webserviceMeta = false;

    var $user;

    static $_cached_module_meta;

    /**
     * creates an instance of Vtiger_ModuleMeta_Model
     *
     * @param <String> $name - module name
     * @param <Object> $user - Users Object
     *
     * @return Vtiger_ModuleMeta_Model
     */
    public static function getInstance($name, $user)
    {
        $moduleMetaClassName = Vtiger_Loader::getComponentClassName('Model', 'ModuleMeta', $name);
        $self = new $moduleMetaClassName();
        $self->moduleName = $name;
        $self->user = $user;

        if (!empty(self::$_cached_module_meta[$name][$user->id])) {
            $self->webserviceMeta = self::$_cached_module_meta[$name][$user->id];

            return $self;
        }

        $handler = vtws_getModuleHandlerFromName($self->moduleName, $user);
        $self->webserviceMeta = $handler->getMeta();
        self::$_cached_module_meta[$name][$user->id] = $self->webserviceMeta;

        return $self;
    }

    /**
     * Functions returns webservices meta object
     * @return webservices meta
     */
    public function getMeta()
    {
        return $this->webserviceMeta;
    }

    /**
     * Function returns list of fields based on type
     *
     * @param <type> $type
     *
     * @return <type>
     */
    public function getFieldListByType($type)
    {
        $meta = $this->getMeta();

        return $meta->getFieldListByType($type);
    }

    /**
     * Function returns accessible fields in a module
     * @return <Array of Vtiger_Field>
     */
    public function getAccessibleFields()
    {
        $meta = self::$_cached_module_meta[$this->moduleName][$this->user->id];
        $moduleFields = $meta->getModuleFields();
        $accessibleFields = [];
        foreach ($moduleFields as $fieldName => $fieldInstance) {
            if ($fieldInstance->getPresence() === 1) {
                continue;
            }
            $accessibleFields[$fieldName] = $fieldInstance;
        }

        return $accessibleFields;
    }

    /**
     * Function returns mergable fields in the module
     * @return <Array of Vtiger_field>
     */
    public function getMergableFields()
    {
        $accessibleFields = $this->getAccessibleFields($this->moduleName);
        $mergableFields = [];
        foreach ($accessibleFields as $fieldName => $fieldInstance) {
            if (intval($fieldInstance->getPresence()) === 1) {
                continue;
            }
            // We need to avoid Last Modified by or any such User reference field
            // for now as Query Generator is not handling it well enough.
            // The case in which query generator is failing to generate right query is,
            // Assigned User field is not there either in the selected fields list or in the conditions
            // and condition is added on the User reference field
            // TODO - Cleanup this once Query Generator support is corrected
            if ($fieldInstance->getFieldDataType() == 'reference') {
                $referencedModules = $fieldInstance->getReferenceList();
                if ($referencedModules[0] == 'Users') {
                    continue;
                }
            }
            $mergableFields[$fieldName] = $fieldInstance;
        }

        return $mergableFields;
    }

    /**
     * Function returns mandatory importable fields
     * @return <Array of Vtiger_Field>
     */
    public function getMandatoryImportableFields()
    {
        $focus = CRMEntity::getInstance($this->moduleName);
        if (method_exists($focus, 'getMandatoryImportableFields')) {
            $mandatoryFields = $focus->getMandatoryImportableFields();
        } else {
            $moduleFields = $this->getAccessibleFields($this->moduleName);
            $mandatoryFields = [];
            foreach ($moduleFields as $fieldName => $fieldInstance) {
                if ($fieldInstance->isMandatory() && $fieldInstance->getFieldDataType() != 'owner'
                    && $this->isEditableField($fieldInstance)) {
                    $mandatoryFields[$fieldName] = $fieldInstance->getFieldLabelKey();
                }
            }
        }

        return $mandatoryFields;
    }

    /**
     * Function returns importable fields
     * @return <Array of Vtiger_Field>
     */
    public function getImportableFields()
    {
        $moduleName = $this->moduleName;
        $focus = CRMEntity::getInstance($moduleName);
        if (method_exists($focus, 'getImportableFields')) {
            $importableFields = $focus->getImportableFields();
        } else {
            $moduleFields = $this->getAccessibleFields($moduleName);
            $importableFields = [];
            foreach ($moduleFields as $fieldName => $fieldInstance) {
                if (($this->isEditableField($fieldInstance)) || ($fieldInstance->getUIType() == 4) && ($fieldInstance->getTableName(
                        ) != 'vtiger_crmentity' || $fieldInstance->getColumnName() != 'modifiedby')
                    || ($fieldInstance->getUIType() == '70' && $fieldName != 'modifiedtime')) {
                    $importableFields[$fieldName] = $fieldInstance;
                }
            }
        }

        if ($moduleName) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $moduleImportFields = $moduleModel->getAdditionalImportFields();
            foreach ($moduleImportFields as $fieldName => $fieldModel) {
                $importableFields[$fieldName] = $fieldModel->getWebserviceFieldObject();
            }
        }

        return $importableFields;
    }

    /**
     * Function returns Entity Name fields
     * @return <Array of Vtiger_Field>
     */
    public function getEntityFields()
    {
        $moduleFields = $this->getAccessibleFields($this->moduleName);
        $entityColumnNames = vtws_getEntityNameFields($this->moduleName);
        $entityNameFields = [];
        foreach ($moduleFields as $fieldName => $fieldInstance) {
            $fieldColumnName = $fieldInstance->getColumnName();
            if (in_array($fieldColumnName, $entityColumnNames)) {
                $entityNameFields[$fieldName] = $fieldInstance;
            }
        }

        return $entityNameFields;
    }

    /**
     * Function checks if the field is editable
     *
     * @param type $fieldInstance
     *
     * @return boolean
     */
    public function isEditableField($fieldInstance)
    {
        if (((int)$fieldInstance->getDisplayType()) === 2 ||
            in_array($fieldInstance->getPresence(), [1, 3]) ||
            strcasecmp($fieldInstance->getFieldDataType(), "autogenerated") === 0 ||
            strcasecmp($fieldInstance->getFieldDataType(), "id") === 0 ||
            $fieldInstance->isReadOnly() == true ||
            $fieldInstance->getUIType() == 70 ||
            $fieldInstance->getUIType() == 4) {
            return false;
        }

        return true;
    }

    /**
     * Function returns list of mandatory fields
     * @return <Array of Vtiger_Field>
     */
    public function getMandatoryFields()
    {
        $focus = CRMEntity::getInstance($this->moduleName);
        if (method_exists($focus, 'getMandatoryImportableFields')) {
            $mandatoryFields = $focus->getMandatoryImportableFields();
        } else {
            $moduleFields = $this->getAccessibleFields($this->moduleName);
            $mandatoryFields = [];
            foreach ($moduleFields as $fieldName => $fieldInstance) {
                if ($fieldInstance->isMandatory()
                    && $fieldInstance->getFieldDataType() != 'owner'
                    && $this->isEditableField($fieldInstance)) {
                    $mandatoryFields[$fieldName] = vtranslate($fieldInstance->getFieldLabelKey(), $this->moduleName);
                }
            }
        }

        return $mandatoryFields;
    }
}