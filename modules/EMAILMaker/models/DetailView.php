<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_DetailView_Model extends Vtiger_DetailView_Model
{
    public static function getInstance($moduleName, $recordId)
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
        $instance = new $modelClassName();
        $moduleModel = EMAILMaker_Module_Model::getInstance($moduleName);
        $recordModel = EMAILMaker_Record_Model::getInstanceById($recordId, $moduleName);

        return $instance->setModule($moduleModel)->setRecord($recordModel);
    }

    public function getDetailViewLinks($linkParams)
    {
        $linkTypes = ['DETAILVIEWBASIC', 'DETAILVIEW'];
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $links = [];
        $links[] = [
            'linktype'  => 'DETAILVIEWBASIC',
            'linklabel' => 'LBL_EDIT',
            'linkurl'   => $recordModel->getEditViewUrl(),
            'linkicon'  => '<i class="fa-solid fa-pencil"></i>',
        ];
        $links[] = [
            'linktype'  => 'DETAILVIEW',
            'linklabel' => 'LBL_DELETE',
            'linkurl'   => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
            'linkicon'  => '<i class="fa-solid fa-trash"></i>',
        ];
        $links[] = [
            'linktype'  => 'DETAILVIEW',
            'linklabel' => 'LBL_DUPLICATE',
            'linkurl'   => $recordModel->getDuplicateRecordUrl(),
            'linkicon'  => '<i class="fa-solid fa-copy"></i>',
        ];

        $linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        return Vtiger_Link_Model::merge($linkModelListDetails, Vtiger_Link_Model::checkAndConvertLinks($links));
    }

    public function getSideBarLinks($linkParams)
    {
        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);
        $listLinkTypes = ['DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET'];
        $listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

        if ($listLinks['DETAILVIEWSIDEBARLINK']) {
            foreach ($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
                $link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
                $moduleLinks['SIDEBARLINK'][] = $link;
            }
        }
        if ($listLinks['DETAILVIEWSIDEBARWIDGET']) {
            foreach ($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
                $link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
                $moduleLinks['SIDEBARWIDGET'][] = $link;
            }
        }

        return $moduleLinks;
    }
}