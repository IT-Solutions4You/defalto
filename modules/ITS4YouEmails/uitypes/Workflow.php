<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Workflow_UIType extends Vtiger_Base_UIType
{
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if (!empty($value)) {
            $workflow = Settings_Workflows_Record_Model::getInstance($value);
            $value = $workflow ? '<a href="' . $workflow->getEditViewUrl() . '&mode=V7Edit">' . $workflow->get('name') . '</a>' : '';
        }

        return $value;
    }
}