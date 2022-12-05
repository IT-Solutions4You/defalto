<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_DetailView_Model extends Vtiger_DetailView_Model
{
    public function getDetailViewLinks($linkParams)
    {
        $linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
        $moduleModel = $this->getModule();
        $linkModelList = array();
        $linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        foreach ($linkTypes as $linkType) {
            if (!empty($linkModelListDetails[$linkType])) {
                foreach ($linkModelListDetails[$linkType] as $linkModel) {
                    // Remove view history, needed in vtiger5 to see history but not in vtiger6
                    if ('View History' === $linkModel->linklabel) {
                        continue;
                    }
                    $linkModelList[$linkType][] = $linkModel;
                }
            }
            unset($linkModelListDetails[$linkType]);
        }

        $relatedLinks = $this->getDetailViewRelatedLinks();

        foreach ($relatedLinks as $relatedLinkEntry) {
            $relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
            $linkModelList[$relatedLink->getType()][] = $relatedLink;
        }

        $widgets = $this->getWidgets();

        foreach ($widgets as $widgetLinkModel) {
            $linkModelList['DETAILVIEWWIDGET'][] = $widgetLinkModel;
        }

        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = $moduleModel->getSettingLinks();

            foreach ($settingsLinks as $settingsLink) {
                $linkModelList['DETAILVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }

        return $linkModelList;
    }
}