<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_DetailView_Model extends Vtiger_DetailView_Model
{
    public function getDetailViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $linkTypes = ['DETAILVIEWBASIC', 'DETAILVIEW'];
        $moduleModel = $this->getModule();
        $links = [];
        $linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        foreach ($linkModelListDetails as $linkTypes) {
            foreach ($linkTypes as $linkModel) {
                $links[] = $linkModel;
            }
        }

        foreach ($this->getDetailViewRelatedLinks() as $relatedLinkEntry) {
            $links[] = $relatedLinkEntry;
        }

        $links[] = $this->getKeyFieldsWidgetInfo();
        $links[] = [
            'linktype'  => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_MESSAGE',
            'linkurl'   => 'module=ITS4YouEmails&view=BodyWidget&record=' . $this->getRecord()->getId(),
            'linkicon'  => '',
        ];
        $links[] = [
            'linktype'  => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_ATTACHMENTS',
            'linkurl'   => 'module=ITS4YouEmails&view=AttachmentsWidget&record=' . $this->getRecord()->getId(),
            'linkicon'  => '',
        ];

        if ($currentUserModel->isAdminUser()) {
            foreach ($moduleModel->getSettingLinks() as $settingsLink) {
                $links[] = $settingsLink;
            }
        }

        $links[] = [
            'linktype'  => 'DETAILVIEWBASIC',
            'linklabel' => 'Reply to',
            'linkurl'   => 'javascript:ITS4YouEmails_MassEdit_Js.replyEmail(' . $this->getRecord()->getId() . ',"' . $this->getModule()->getName() . '");',
            'linkicon'  => '<i class="fa-solid fa-reply"></i>',
        ];
        $links[] = [
            'linktype'  => 'DETAILVIEWBASIC',
            'linklabel' => 'Reply to all',
            'linkurl'   => 'javascript:ITS4YouEmails_MassEdit_Js.replyAllEmail(' . $this->getRecord()->getId() . ',"' . $this->getModule()->getName() . '");',
            'linkicon'  => '<i class="fa-solid fa-reply-all"></i>',
        ];
        $links[] = [
            'linktype'  => 'DETAILVIEWBASIC',
            'linklabel' => 'Forward',
            'linkurl'   => 'javascript:ITS4YouEmails_MassEdit_Js.forwardEmail(' . $this->getRecord()->getId() . ',"' . $this->getModule()->getName() . '");',
            'linkicon'  => '<i class="fa-solid fa-reply fa-flip-horizontal"></i>',
        ];

        return Vtiger_Link_Model::checkAndConvertLinks($links);
    }
}