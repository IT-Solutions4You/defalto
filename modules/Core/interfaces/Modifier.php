<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

interface Core_Modifier_Interface
{
    /**
     * @param Vtiger_Viewer  $viewer
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function modifyProcess(Vtiger_Viewer $viewer, Vtiger_Request $request): void;
}