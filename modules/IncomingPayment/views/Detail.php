<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Detail_View extends Vtiger_Detail_View
{
    /**
     * Shows Payments as the module name in the page header while the Payments list is available,
     * then renders the standard detail header.
     *
     * @param Vtiger_Request $request
     * @param bool           $display Whether to render the header template
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $breadcrumbLabel = Vtiger_Module_Model::getInstance($request->getModule())->getBreadcrumbLabel();

        if ($breadcrumbLabel !== '') {
            $this->getViewer($request)->assign('MODULE_BREADCRUMB_LABEL', $breadcrumbLabel);
        }

        parent::preProcess($request, $display);
    }
}
