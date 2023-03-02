<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Module_Model extends Vtiger_Module_Model
{
    /**
     * @return array
     */
    public function getModuleBasicLinks(): array
    {
        if ('Calendar' !== $_REQUEST['view']) {
            $basicLinks = [
                [
                    'linktype' => 'BASIC',
                    'linklabel' => 'LBL_CALENDAR',
                    'linkurl' => 'index.php?module=ITS4YouCalendar&view=Calendar',
                    'linkicon' => 'fa-calendar',
                ]
            ];
        } else {
            $basicLinks = [
                [
                    'linktype' => 'BASIC',
                    'linklabel' => 'LBL_LIST',
                    'linkurl' => 'index.php?module=ITS4YouCalendar&view=List',
                    'linkicon' => 'fa-bars',
                ]
            ];
        }

        return array_merge($basicLinks, parent::getModuleBasicLinks());
    }

    /**
     * @return array
     */
    public function getUsersAndGroups(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = (array)$currentUser->getAccessibleUsers();
        $groups = (array)$currentUser->getAccessibleGroups();

        return $users + $groups;
    }
}