<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $moduleName = $moduleModel->getName();
        $recordId = $recordModel->getId();
        $detailViewLink = array();
        $detailViewLinks[] = array(
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'LBL_EDIT',
            'linkurl' => $recordModel->getEditViewUrl(),
            'linkicon' => '<i class="fa-solid fa-pencil"></i>'
        );

        foreach ($detailViewLinks as $detailViewLink) {
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
        }

        $linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
        $detailViewBasiclinks = $linkModelListDetails['DETAILVIEWBASIC'];
        unset($linkModelListDetails['DETAILVIEWBASIC']);

        $deletelinkModel = array(
            'linktype' => 'DETAILVIEW',
            'linklabel' => 'LBL_DELETE',
            'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
            'linkicon' => '<i class="fa-solid fa-trash"></i>'
        );
        $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);

        $duplicateLinkModel = array(
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'LBL_DUPLICATE',
            'linkurl' => $recordModel->getDuplicateRecordUrl(),
            'linkicon' => '<i class="fa-solid fa-copy"></i>'
        );
        $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);

        if (!empty($detailViewBasiclinks)) {
            foreach ($detailViewBasiclinks as $linkModel) {
                $linkModelList['DETAILVIEW'][] = $linkModel;
            }
        }
        return $linkModelList;
    }

    public function getSideBarLinks($linkParams)
    {
        $linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
        $moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);
        $listLinkTypes = array('DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET');
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