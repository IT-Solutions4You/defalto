<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Vtiger EditView Model Class
 */
class Vtiger_EditView_Model extends Vtiger_Base_Model
{
    /**
     * Function to get the Module Model
     * @return Vtiger_Module_Model instance
     */
    public function getModule()
    {
        return $this->get('module');
    }

    /**
     * Function to get the list of listview links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
     */
    public function getEditViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $linkTypes = ['LISTVIEWQUICK', 'LISTVIEWQUICKWIDGET', 'LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING'];
        $links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $linkTypes, $linkParams);

        $quickLinks = [
            [
                'linktype'  => 'LISTVIEWQUICK',
                'linklabel' => 'Dashboard',
                'linkurl'   => $this->getModule()->getDefaultUrl(),
                'linkicon'  => ''
            ],
            [
                'linktype'  => 'LISTVIEWQUICK',
                'linklabel' => $this->getModule()->get('label') . ' List',
                'linkurl'   => $this->getModule()->getDefaultUrl(),
                'linkicon'  => ''
            ],
        ];
        foreach ($quickLinks as $quickLink) {
            $links['LISTVIEWQUICK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        $quickWidgets = [
            [
                'linktype'  => 'LISTVIEWQUICKWIDGET',
                'linklabel' => 'Active ' . $this->getModule()->get('label'),
                'linkurl'   => 'module=' . $this->getModule()->get('name') . '&view=List&mode=showActiveRecords',
                'linkicon'  => ''
            ]
        ];
        foreach ($quickWidgets as $quickWidget) {
            $links['LISTVIEWQUICKWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
        }

        $basicLinks = [
            [
                'linktype'  => 'LISTVIEWBASIC',
                'linklabel' => 'Add ' . $this->getModule()->get('name'),
                'linkurl'   => $this->getModule()->getCreateRecordUrl(),
                'linkicon'  => ''
            ]
        ];
        foreach ($basicLinks as $basicLink) {
            $links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
        }

        $advancedLinks = [
            [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'Import',
                'linkurl'   => $this->getModule()->getImportUrl(),
                'linkicon'  => ''
            ],
            [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'Export',
                'linkurl'   => $this->getModule()->getExportUrl(),
                'linkicon'  => ''
            ],
            [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'Find Duplicates',
                'linkurl'   => $this->getModule()->getFindDuplicatesUrl(),
                'linkicon'  => ''
            ]
        ];
        foreach ($advancedLinks as $advancedLink) {
            $links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
        }

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = [
                [
                    'linktype'  => 'LISTVIEWSETTING',
                    'linklabel' => 'Edit Fields',
                    'linkurl'   => $this->getModule()->getSettingsUrl('LayoutEditor'),
                    'linkicon'  => ''
                ],
                [
                    'linktype'  => 'LISTVIEWSETTING',
                    'linklabel' => 'Edit Workflows',
                    'linkurl'   => $this->getModule()->getSettingsUrl('EditWorkflows'),
                    'linkicon'  => ''
                ],
                [
                    'linktype'  => 'LISTVIEWSETTING',
                    'linklabel' => 'Edit Picklist Values',
                    'linkurl'   => $this->getModule()->getSettingsUrl('PicklistEditor'),
                    'linkicon'  => ''
                ]
            ];
            foreach ($settingsLinks as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }

        return $links;
    }
}