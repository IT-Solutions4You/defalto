<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Documents_Field_Model extends Vtiger_Field_Model {
	/**
	 * Function to retieve display value for a value
	 * @param <String> $value - value which need to be converted to display value
	 * @return <String> - converted display value
	 */
	public function getDisplayValue($value, $record=false, $recordInstance = false) {
		$fieldName = $this->getName();

		if($fieldName == 'filesize' && $recordInstance) {
			$downloadType = $recordInstance->get('filelocationtype');
			if($downloadType == 'I') {
				$filesize = $value;
				if($filesize < 1024)
					$value=$filesize.' B';
				elseif($filesize > 1024 && $filesize < 1048576)
					$value=round($filesize/1024,2).' KB';
				else if($filesize > 1048576)
					$value=round($filesize/(1024*1024),2).' MB';
			} else {
				$value = ' --';
			}
			return $value;
		}

		return parent::getDisplayValue($value, $record, $recordInstance);
	}
    
    public function hasCustomLock() {
        $fieldsToLock = array('filename','notecontent','folderid','document_source','filelocationtype');
        if(in_array($this->getName(), $fieldsToLock)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the field is editable via AJAX.
     *
     * @return bool
     * @throws Exception
     */
    public function isAjaxEditable()
    {
        $isParentEditable = parent::isAjaxEditable();
        $fieldType = $this->get('uitype');

        return $isParentEditable && !in_array($fieldType, [self::UITYPE_CKEDITOR, self::UITYPE_FULL_WIDTH_TEXT_AREA, self::UITYPE_FOLDER_NAME]);
    }
}