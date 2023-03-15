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
     * @param Vtiger_Request $request
     * @return void
     */
    public static function retrieveDefaultValuesForEdit(Vtiger_Request $request)
    {
        if ($request->isEmpty('record')) {
            $currentUser = Users_Record_Model::getCurrentUserModel();

            $request->set('calendar_status', $currentUser->get('defaulteventstatus'));
            $request->set('calendar_type', $currentUser->get('defaultactivitytype'));
        }
    }

    /**
     * @return array
     */
    public function getModuleBasicLinks(): array
    {
        $basicLinks = [];

        if ('Calendar' !== $_REQUEST['view']) {
            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_CALENDAR',
                'linkurl' => 'index.php?module=ITS4YouCalendar&view=Calendar',
                'linkicon' => 'fa-calendar',
            ];
        } else {
            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_LIST',
                'linkurl' => 'index.php?module=ITS4YouCalendar&view=List',
                'linkicon' => 'fa-bars',
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

    /**
     * @return array
     */
    public function getSettingLinks(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $settingsLinks = [];
        $settingsLinks[] = array(
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_CALENDAR_SETTINGS',
            'linkurl' => 'index.php?module=Users&parent=Settings&view=Calendar&record=' . $currentUser->getId(),
        );

        return array_merge($settingsLinks, parent::getSettingLinks());
    }
}