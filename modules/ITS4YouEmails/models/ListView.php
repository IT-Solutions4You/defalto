<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_ListView_Model extends Vtiger_ListView_Model
{
    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = $this->getModule();
        $linkTypes = ['LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING'];
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        foreach ($this->getAdvancedLinks() as $advancedLink) {
            $links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
        }

        if ($currentUserModel->isAdminUser()) {
            foreach ($this->getSettingLinks() as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }

        return $links;
    }
}