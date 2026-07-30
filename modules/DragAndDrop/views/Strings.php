<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class DragAndDrop_Strings_View extends Vtiger_IndexAjax_View
{
    /**
     * The global overlay needs its own module translations on every detail view.
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    public function process(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();

        $response->setResult(Vtiger_Language_Handler::export('DragAndDrop', 'jsLanguageStrings'));
        $response->emit();
    }
}
