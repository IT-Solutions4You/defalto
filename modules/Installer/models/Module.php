<?php

class Installer_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-download';

    public function getDefaultUrl()
    {
        return 'index.php?module=Installer&view=Index';
    }

    public function getListViewLinks(): array
    {
        $links = [];
        $links[] = [
            'linktype' => Vtiger_Link_Model::LINK_LISTVIEWBASIC,
            'linklabel' => 'LBL_INSTALLER',
            'linkurl' => 'index.php?module=Installer&view=Index',
            'linkicon' => 'fa-solid fa-download',
        ];
        $links[] = [
            'linktype' => Vtiger_Link_Model::LINK_LISTVIEWBASIC,
            'linklabel' => 'LBL_REQUIREMENTS',
            'linkurl' => 'index.php?module=Installer&view=Requirements',
            'linkicon' => 'fas fa-cogs',
        ];

        return Vtiger_Link_Model::checkAndConvertLinks($links);
    }
}