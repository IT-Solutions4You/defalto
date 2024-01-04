<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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