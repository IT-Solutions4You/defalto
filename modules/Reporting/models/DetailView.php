<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_DetailView_Model extends Vtiger_DetailView_Model
{
    public array $skipDetailLinkByLabel = ['LBL_ADD_TAG', 'LBL_KEY_FIELDS'];

    public function getDetailViewLinks($linkParams)
    {
        $links = [
            [
                'linktype'  => 'DETAILVIEWWIDGET',
                'linklabel' => 'Reporting',
                'linkurl'   => sprintf('module=Reporting&view=Detail&mode=getReport&record=%d', $linkParams['RECORD']),
            ],
            [
                'linktype'  => 'DETAILVIEWBASIC',
                'linklabel' => 'Export XLS',
                'linkicon'  => '<i class="fa-solid fa-file-excel"></i>',
                'linkurl'   => sprintf('index.php?module=Reporting&view=Detail&mode=getReportXLS&record=%d', $linkParams['RECORD']),
            ],
            [
                'linktype'  => 'DETAILVIEWBASIC',
                'linklabel' => 'Export PDF',
                'linkicon'  => '<i class="fa-solid fa-file-pdf"></i>',
                'linkurl'   => sprintf('index.php?module=Reporting&view=Detail&mode=getReportPDF&record=%d', $linkParams['RECORD']),
            ],
            $this->getTagsLinkInfo(),
        ];

        return Vtiger_Link_Model::merge(parent::getDetailViewLinks($linkParams), Vtiger_Link_Model::checkAndConvertLinks($links));
    }
}