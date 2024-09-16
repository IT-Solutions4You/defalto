<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return Vtiger_Util_Helper::getRecordName($this->getId());
	}

	/**
	 * Function to get URL for Convert FAQ
	 * @return <String>
	 */
	public function getConvertFAQUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ConvertFAQ&record=".$this->getId();
	}

	/**
	 * Function to get Comments List of this Record
	 * @return <String>
	 */
	public function getCommentsList() {
		$db = PearDatabase::getInstance();
		$commentsList = array();

		$result = $db->pquery("SELECT commentcontent AS comments FROM vtiger_modcomments WHERE related_to = ?", array($this->getId()));
		$numOfRows = $db->num_rows($result);

		for ($i=0; $i<$numOfRows; $i++) {
			array_push($commentsList, $db->query_result($result, $i, 'comments'));
		}

		return $commentsList;
	}

    public function getTicketTable()
    {
        return (new Core_DatabaseData_Model())->getTable('vtiger_troubletickets', 'ticketid');
    }

    public function updateCommentFields(): void
    {
        $ticketTable = $this->getTicketTable();
        $ticketId = $this->getId();
        $createdTime = strtotime($this->get('createdtime'));

        if ($this->isEmpty('first_comment')) {
            $firstComment = date('Y-m-d H:i:s');

            $this->set('first_comment', $firstComment);
            $this->set('first_comment_hours', (strtotime($firstComment) - $createdTime) / 60 / 60);
        }

        $lastComment = date('Y-m-d H:i:s');

        $this->set('last_comment', $lastComment);
        $this->set('last_comment_hours', (strtotime($lastComment) - $createdTime) / 60 / 60);

        $ticketTable->updateData(
            [
                'first_comment' => $this->get('first_comment'),
                'first_comment_hours' => $this->get('first_comment_hours'),
                'last_comment' => $this->get('last_comment'),
                'last_comment_hours' => $this->get('last_comment_hours'),
            ],
            [
                $ticketTable->get('table_id') => $ticketId,
            ],
        );
    }
}