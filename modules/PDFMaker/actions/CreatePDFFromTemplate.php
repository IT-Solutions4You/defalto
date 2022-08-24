<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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