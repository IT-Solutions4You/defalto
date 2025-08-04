<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-download';

    public function getDefaultUrl()
    {
        return 'index.php?module=Installer&view=Index';
    }

    public function getListViewUrl()
    {
        return 'index.php?module=Installer&view=Index';
    }

    /**
     * @return array
     */
    public function getSettingLinks()
    {
        return [
            [
                'linktype'  => Vtiger_Link_Model::LINK_LISTVIEWSETTING,
                'linklabel' => 'LBL_INSTALLER',
                'linkurl'   => 'index.php?module=Installer&view=Index',
                'linkicon'  => 'fa-solid fa-download',
            ],
            [
                'linktype'  => Vtiger_Link_Model::LINK_LISTVIEWSETTING,
                'linklabel' => 'LBL_REQUIREMENTS',
                'linkurl'   => 'index.php?module=Installer&view=Requirements',
                'linkicon'  => 'fas fa-cogs',
            ],
        ];
    }

    public function getListViewLinks(): array
    {
        $links = [];
        $links[] = [
            'linktype'  => Vtiger_Link_Model::LINK_LISTVIEWBASIC,
            'linklabel' => 'LBL_INSTALLER',
            'linkurl'   => 'index.php?module=Installer&view=Index',
            'linkicon'  => 'fa-solid fa-download',
        ];
        $links[] = [
            'linktype'  => Vtiger_Link_Model::LINK_LISTVIEWBASIC,
            'linklabel' => 'LBL_REQUIREMENTS',
            'linkurl'   => 'index.php?module=Installer&view=Requirements',
            'linkicon'  => 'fas fa-cogs',
        ];

        return Vtiger_Link_Model::checkAndConvertLinks($links);
    }
}