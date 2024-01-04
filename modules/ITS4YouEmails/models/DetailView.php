<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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

        $attachments = array(
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_ATTACHMENTS',
            'linkurl' => 'module=ITS4YouEmails&view=AttachmentsWidget&record=' . $this->getRecord()->getId(),
            'linkicon' => ''
        );
        $linkModelList['DETAILVIEWWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($attachments);

        $body = array(
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_MESSAGE',
            'linkurl' => 'module=ITS4YouEmails&view=BodyWidget&record=' . $this->getRecord()->getId(),
            'linkicon' => ''
        );
        $linkModelList['DETAILVIEWWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($body);

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