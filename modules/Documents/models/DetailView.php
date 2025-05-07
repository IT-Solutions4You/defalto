<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
    public function getDetailViewLinks($linkParams)
    {
        $recordModel = $this->getRecord();
        $links = [];

        if ('I' === $recordModel->get('filelocationtype') && php7_count($recordModel->getFileDetails())) {
            $links[] = [
                'linktype' => Vtiger_DetailView_Model::LINK_ADVANCED,
                'linklabel' => 'LBL_VIEW_FILE',
                'linkicon' => '<i class="fa-solid fa-eye"></i>',
                'linkurl' => 'Vtiger_Header_Js.previewFile(event,' . $recordModel->getId() . ')',
                'filelocationtype' => $recordModel->get('filelocationtype'),
                'filename' => $recordModel->get('filename'),
            ];
            $links[] = [
                'linktype' => Vtiger_DetailView_Model::LINK_MORE,
                'linklabel' => 'LBL_DOWNLOAD_FILE',
                'linkurl' => $recordModel->getDownloadFileURL(),
                'linkicon' => '<i class="fa-solid fa-download"></i>',
            ];
            $links[] = [
                'linktype' => Vtiger_DetailView_Model::LINK_MORE,
                'linklabel' => 'LBL_CHECK_FILE_INTEGRITY',
                'linkurl' => $recordModel->checkFileIntegrityURL(),
                'linkicon' => '<i class="fa-solid fa-file-circle-check"></i>',
            ];
        }

        return Vtiger_Link_Model::merge(parent::getDetailViewLinks($linkParams), Vtiger_Link_Model::checkAndConvertLinks($links));
    }

}
