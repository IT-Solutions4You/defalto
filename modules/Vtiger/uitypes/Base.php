<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Base_UIType extends Vtiger_Base_Model {

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/String.tpl';
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDBInsertValue($value) {
		return $value;
	}

	/**
	 * Function to get the Value of the field in the format, the user provides it on Save
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getUserRequestValue($value) {
		return $value;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record=false, $recordInstance=false) {
		return $value;
	}

    /**
     * Static function to get the UIType object from Vtiger Field Model
     * @param Vtiger_Field_Model $fieldModel
     * @return Vtiger_Base_UIType or UIType specific object instance
     * @throws AppException
     * @throws Exception
     */
    public static function getInstanceFromField($fieldModel): Vtiger_Base_UIType
    {
        $fieldDataType = $fieldModel->getFieldDataType();
        $uiTypeClassSuffix = ucfirst($fieldDataType);
        $moduleName = $fieldModel->getModuleName();
        $moduleSpecificFilePath = Vtiger_Loader::getComponentClassName('UIType', $uiTypeClassSuffix, $moduleName);

        if (class_exists($moduleSpecificFilePath)) {
            $instance = new $moduleSpecificFilePath();
        } else {
            $instance = new Vtiger_Base_UIType();
        }

        $instance->set('field', $fieldModel);

        return $instance;
    }

    /**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value) {
		return $value;
	}

    /**
	 * Function to get the Detailview template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getDetailViewTemplateName() {
		return 'uitypes/StringDetailView.tpl';
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($value) {
		return $this->getDisplayValue($value);
	}
    
    public function getListSearchTemplateName() {
        return 'uitypes/FieldSearchView.tpl';
    }

    public function isLabelTemplate(): bool
    {
        return false;
    }
}