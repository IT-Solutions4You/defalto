<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Field_Model extends Vtiger_Field_Model
{
    public $customDataTypes = [
        'workflow_id' => 'Workflow',
    ];

    public function isAjaxEditable()
    {
        return false;
    }

    public function getFieldDataType()
    {
        if (isset($this->customDataTypes[$this->name])) {
            return $this->customDataTypes[$this->name];
        }

        return parent::getFieldDataType();
    }
}