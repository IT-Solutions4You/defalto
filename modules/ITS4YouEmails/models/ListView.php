<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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