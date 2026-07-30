<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class DragAndDrop_Overlay_View extends Vtiger_IndexAjax_View
{
    /**
     * The global header script renders this shell on detail views of other modules.
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $this->getViewer($request)
            ->assign('MODULE', 'DragAndDrop')
            ->assign('DRAG_AND_DROP_STRINGS', Vtiger_Language_Handler::export('DragAndDrop', 'jsLanguageStrings'))
            ->view('Overlay.tpl', 'DragAndDrop');
    }
}
