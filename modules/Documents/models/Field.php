<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
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

        return $isParentEditable && !in_array($fieldType, [self::UITYPE_CKEDITOR, self::UITYPE_FULL_WIDTH_TEXT_AREA]);
    }
}