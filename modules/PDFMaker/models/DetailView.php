<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_DetailView_Model extends Vtiger_DetailView_Model
{
    public static function getInstance($moduleName, $recordId)
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
        $instance = new $modelClassName();

        $moduleModel = PDFMaker_Module_Model::getInstance($moduleName);
        $recordModel = PDFMaker_Record_Model::getInstanceById($recordId, $moduleName);

        return $instance->setModule($moduleModel)->setRecord($recordModel);
    }

    public function getDetailViewLinks($linkParams)
    {
        $linkTypes = ['DETAILVIEWBASIC', 'DETAILVIEW'];
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();

        $detailViewLink = Vtiger_Link_Model::getInstanceFromValues([
            'linktype'  => Vtiger_DetailView_Model::LINK_ADVANCED,
            'linklabel' => 'LBL_EDIT',
            'linkurl'   => $recordModel->getEditViewUrl(),
            'linkicon'  => '<i class="fa fa-pencil"></i>'
        ]);

        $linkModelList = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
        $linkModelList[$detailViewLink->get('linktype')][] = $detailViewLink;

        return $linkModelList;
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