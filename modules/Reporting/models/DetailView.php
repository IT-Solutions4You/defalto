<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_DetailView_Model extends Vtiger_DetailView_Model
{
    public function getDetailViewLinks($linkParams)
    {
        $links = parent::getDetailViewLinks($linkParams);
        $links['DETAILVIEWWIDGET'] = [];
        $links['DETAILVIEWWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'Reporting',
            'linkurl' => sprintf('module=Reporting&view=Detail&mode=getReport&record=%d', $linkParams['RECORD']),
        ]);
        $links['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'Export XLS',
            'linkicon' => '<i class="fa-solid fa-file-excel"></i>',
            'linkurl' => sprintf('index.php?module=Reporting&view=Detail&mode=getReportXLS&record=%d', $linkParams['RECORD']),
        ]);
        $links['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
            'linktype' => 'DETAILVIEWBASIC',
            'linklabel' => 'Export PDF',
            'linkicon' => '<i class="fa-solid fa-file-pdf"></i>',
            'linkurl' => sprintf('index.php?module=Reporting&view=Detail&mode=getReportPDF&record=%d', $linkParams['RECORD']),
        ]);

        return $links;
    }

}