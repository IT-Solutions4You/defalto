<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_AiAvailability_View extends Vtiger_Basic_View
{
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    public function postProcess(Vtiger_Request $request): void
    {
    }

    public function process(Vtiger_Request $request): void
    {
        $status = (new Core_AiAvailability_Model())->getStatus();

        $this->getViewer($request)
            ->assign('AI_STATUS', $status)
            ->view('AiAvailability.tpl', 'Core');
    }
}
