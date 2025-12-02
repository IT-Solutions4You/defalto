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

class Vtiger_Base_UIType extends Vtiger_Base_Model
{
    /**
     * @param mixed       $value
     * @param bool|int    $record
     * @param object|bool $recordInstance
     *
     * @return string
     * @throws Exception
     */
    public function getReportDisplayValue(mixed $value, bool|int $record, object|bool $recordInstance): string
    {
        $displayValue = $this->getDisplayValue($value, $record, $recordInstance);

        if ($recordInstance && $this->isReportValueUrl()) {
            return sprintf('<a href="%s">%s</a>', $recordInstance->getDetailViewUrl(), strip_tags($displayValue));
        }

        return $displayValue;
    }

    /**
     * @param mixed       $value
     * @param bool|int    $record
     * @param object|bool $recordInstance
     *
     * @return string
     * @throws Exception
     */
    public function getRelatedBlockDisplayValue(mixed $value, bool|int $record, object|bool $recordInstance): string
    {
        return $this->getDisplayValue($value, $record, $recordInstance);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isReportValueUrl(): bool
    {
        /** @var Vtiger_Field_Model $field */
        $field = $this->get('field');

        return 4 === $field->getUIType() || $field->isNameField();
    }

    /**
     * Function to get the Template name for the current UI Type Object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/String.tpl';
    }

    /**
     * Function to get the DB Insert Value, for the current field type with given User Value
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getDBInsertValue($value)
    {
        return $value;
    }

    /**
     * Function to get the Value of the field in the format, the user provides it on Save
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getUserRequestValue($value)
    {
        return $value;
    }

    /**
     * @param mixed $fieldValue
     *
     * @return mixed
     */
    public function getRequestValue(mixed $fieldValue): mixed
    {
        $fieldModel = $this->getFieldModel();
        $fieldName = $fieldModel->getName();
        $fieldDataType = $fieldModel->getFieldDataType();

        if (null === $fieldValue) {
            return null;
        }

        if (in_array($fieldName, ['commentcontent', 'notecontent'])) {
            $purifiedContent = vtlib_purify(decode_html($fieldValue));
            // Purify malicious html event attributes
            $fieldValue = purifyHtmlEventAttributes(decode_html($purifiedContent), true);
        }

        if ('multipicklist' === $fieldDataType && is_array($fieldValue)) {
            $fieldValue = implode(' |##| ', $fieldValue);
        }

        if ('time' === $fieldDataType) {
            $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
        }

        if ('currency' !== $fieldDataType && !is_array($fieldValue)) {
            $fieldValue = trim($fieldValue);
        }

        return Vtiger_Util_Helper::validateFieldValue($fieldValue, $fieldModel);
    }

    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return $value;
    }

    /**
     * Static function to get the UIType object from Vtiger Field Model
     *
     * @param Vtiger_Field_Model $fieldModel
     *
     * @return Vtiger_Base_UIType or UIType specific object instance
     * @throws Exception
     */
    public static function getInstanceFromField($fieldModel): Vtiger_Base_UIType
    {
        $fieldDataType = $fieldModel->getFieldDataType();
        $moduleName = $fieldModel->getModuleName();
        $instance = self::getInstance($fieldDataType, $moduleName);
        $instance->set('field', $fieldModel);

        return $instance;
    }

    /**
     * @param string $fieldDataType
     * @param string $moduleName
     * @return Vtiger_Base_UIType
     * @throws Exception
     */
    public static function getInstance(string $fieldDataType, string $moduleName = 'Vtiger'): Vtiger_Base_UIType
    {
        $uiTypeClassSuffix = ucfirst($fieldDataType);
        $moduleSpecificFilePath = Vtiger_Loader::getComponentClassName('UIType', $uiTypeClassSuffix, $moduleName);

        if (class_exists($moduleSpecificFilePath)) {
            $instance = new $moduleSpecificFilePath();
        } else {
            $instance = new Vtiger_Base_UIType();
        }

        return $instance;
    }

    /**
     * @return object
     */
    public function getFieldModel(): object
    {
        return $this->get('field');
    }

    /**
     * Function to get the display value in edit view
     *
     * @param reference record id
     *
     * @return link
     */
    public function getEditViewDisplayValue($value)
    {
        return $value;
    }

    /**
     * Function to get the Detailview template name for the current UI Type Object
     * @return <String> - Template Name
     */
    public function getDetailViewTemplateName()
    {
        return 'uitypes/StringDetailView.tpl';
    }

    /**
     * Function to get Display value for RelatedList
     *
     * @param <String> $value
     *
     * @return <String>
     */
    public function getRelatedListDisplayValue($value)
    {
        return $this->getDisplayValue($value);
    }

    public function getListSearchTemplateName()
    {
        return 'uitypes/FieldSearchView.tpl';
    }

    public function isLabelTemplate(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isItemDetailField(): bool
    {
        return in_array($this->getFieldModel()->block->label, ['LBL_INVENTORYITEM_INFORMATION', 'LBL_ITEM_DETAILS']);
    }

    public function getNumberUser(): object
    {
        $this->retrieveNumberUser();

        return vglobal('number_user');
    }

    /**
     * @return object
     */
    public function getInventoryNumberUser(): object
    {
        $this->retrieveInventoryNumberUser();

        return vglobal('inventory_number_user');
    }

    public function retrieveNumberUser(): void
    {
        if (vglobal('number_user')) {
            return;
        }

        $user = clone Users_Record_Model::getCurrentUserModel();
        $user->set('currency_decimal_separator', '.');
        $user->set('currency_grouping_separator', '');

        vglobal('number_user', $user);
    }

    /**
     * @return void
     */
    public function retrieveInventoryNumberUser(): void
    {
        if (vglobal('inventory_number_user')) {
            return;
        }

        $decimalPlaces = InventoryItem_Utils_Helper::fetchDecimals();
        $user = clone Users_Record_Model::getCurrentUserModel();
        $user->set('no_of_currency_decimals', $decimalPlaces['quantity']);
        $user->set('truncate_trailing_zeros', false);

        vglobal('inventory_number_user', $user);
    }


    public function getCurrencyUser(): object
    {
        $this->retrieveCurrencyUser();

        return vglobal('currency_user');
    }

    /**
     * @return object
     */
    public function getInventoryCurrencyUser(): object
    {
        $this->retrieveInventoryCurrencyUser();

        return vglobal('inventory_currency_user');
    }

    public function retrieveCurrencyUser(): void
    {
        if (vglobal('currency_user')) {
            return;
        }

        $user = clone Users_Record_Model::getCurrentUserModel();
        vglobal('currency_user', $user);
    }

    /**
     * @return void
     */
    public function retrieveInventoryCurrencyUser(): void
    {
        if (vglobal('inventory_currency_user')) {
            return;
        }

        $decimalPlaces = InventoryItem_Utils_Helper::fetchDecimals();

        $user = clone Users_Record_Model::getCurrentUserModel();
        $user->set('no_of_currency_decimals', $decimalPlaces['price']);

        vglobal('inventory_currency_user', $user);
    }
}