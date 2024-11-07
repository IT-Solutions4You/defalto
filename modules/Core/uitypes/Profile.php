<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Profile_UiType extends Vtiger_Base_UIType {
    /**
     * Function to get the Template name for the current UI Type Object
     * @return string - Template Name
     */
    public function getTemplateName(): string
    {
        return 'uitypes/Profile.tpl';
    }

    /**
     * Function to get the display value in detail view
     *
     * @param mixed crmid of record
     *
     * @return mixed
     */
    public function getEditViewDisplayValue($value)
    {
        if (empty($value)) {
            return '';
        }

        $profileModel = Settings_Profiles_Record_Model::getInstanceById($value);

        return $profileModel->getName();
    }

    /**
     * Function to get display value
     *
     * @param <String> $value
     * @param bool|int $recordId
     * @param bool|object $recordInstance
     * @return mixed|string display value
     */
    public function getDisplayValue($value, $recordId = false, $recordInstance = false)
    {
        $displayValue = $this->getEditViewDisplayValue($value);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $roleRecordModel = new Settings_Profiles_Record_Model();
            $roleRecordModel->set('profileid', $value);

            return '<a href="' . $roleRecordModel->getEditViewUrl() . '">' . textlength_check($displayValue) . '</a>';
        }

        return $displayValue;
    }
}