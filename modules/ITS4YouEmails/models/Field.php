<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

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