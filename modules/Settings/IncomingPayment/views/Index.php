<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_IncomingPayment_Index_View extends Settings_Vtiger_Index_View
{
    /**
     * Renders the integration settings of incoming payments for every available document module.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $rows = IncomingPayment_IntegrationFields_Helper::getRows();

        $this->getViewer($request)
            ->assign('MODULE', $request->getModule())
            ->assign('QUALIFIED_MODULE', $qualifiedModuleName)
            ->assign('INTEGRATION_ROWS', $rows)
            ->assign('INTEGRATION_COLUMNS', IncomingPayment_Config_Helper::INTEGRATION_COLUMNS)
            ->assign('UNDERPAYMENT_THRESHOLD', IncomingPayment_Setting_Model::getInstance()->getUnderpaymentThreshold())
            ->view('Index.tpl', $qualifiedModuleName);
    }
}
