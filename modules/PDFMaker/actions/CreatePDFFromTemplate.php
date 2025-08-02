<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_CreatePDFFromTemplate_Action extends Vtiger_Action_Controller
{
    function __construct()
    {
    }

    public function checkPermission(Vtiger_Request $request)
    {
    }

    function process(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $checkGenerate = new PDFMaker_checkGenerate_Model();
        $checkGenerate->generate($request);
    }
}