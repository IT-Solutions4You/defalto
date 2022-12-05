<?php

class ITS4YouEmails_Workflow_UIType extends Vtiger_Base_UIType {
	public function getDisplayValue($value, $record = false, $recordInstance = false)
	{
		if(!empty($value)) {
			$workflow = Settings_Workflows_Record_Model::getInstance($value);
			$value = $workflow ? '<a href="' . $workflow->getEditViewUrl() . '&mode=V7Edit">' . $workflow->get('name') . '</a>' : '';
		}

		return $value;
	}
}