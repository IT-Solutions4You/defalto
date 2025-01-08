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
        $widgets[] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_MESSAGE',
            'linkurl' => 'module=ITS4YouEmails&view=BodyWidget&record=' . $this->getRecord()->getId(),
            'linkicon' => '',
        ]);
        $widgets[] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_ATTACHMENTS',
            'linkurl' => 'module=ITS4YouEmails&view=AttachmentsWidget&record=' . $this->getRecord()->getId(),
            'linkicon' => '',
        ]);

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

        $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'Reply to',
            'linkurl' => 'javascript:ITS4YouEmails_MassEdit_Js.replyEmail(' . $this->getRecord()->getId() . ',"' . $this->getModule()->getName() . '");',
            'linkicon' => '<i class="fa-solid fa-reply"></i>',
        ]);

        $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'Reply to all',
            'linkurl' => 'javascript:ITS4YouEmails_MassEdit_Js.replyAllEmail(' . $this->getRecord()->getId() . ',"' . $this->getModule()->getName() . '");',
            'linkicon' => '<i class="fa-solid fa-reply-all"></i>',
        ]);

        $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'Forward',
            'linkurl' => 'javascript:ITS4YouEmails_MassEdit_Js.forwardEmail(' . $this->getRecord()->getId() . ',"' . $this->getModule()->getName() . '");',
            'linkicon' => '<i class="fa-solid fa-reply fa-flip-horizontal"></i>',
        ]);

        return $linkModelList;
    }
}