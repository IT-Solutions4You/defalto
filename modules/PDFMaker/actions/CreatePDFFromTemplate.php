<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_CreatePDFFromTemplate_Action extends Core_Controller_Action
{
    function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $request->set('source_module', getSalesEntityType($request->get('record')));

        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'source_module', 'action' => 'DetailView', 'record_parameter' => 'record'];

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        if ('checkRecords' === $request->get('mode')) {
            $this->checkRecords($request);

            return;
        }

        PDFMaker_Debugger_Model::GetInstance()->Init();
        $checkGenerate = new PDFMaker_checkGenerate_Model();
        $checkGenerate->generate($request);
    }

    /**
     * Tells the browser before the download or preview whether a record rule denies the PDF export of the record,
     * so the user sees the reason instead of an error page.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function checkRecords(Vtiger_Request $request): void
    {
        $denyReason = Vtiger_Module_Model::getInstance('PDFMaker')->getPdfExportDenyReason((int)$request->get('record'));

        $response = new Vtiger_Response();
        $response->setResult([
            'denied'  => null !== $denyReason,
            'message' => (string)$denyReason,
        ]);
        $response->emit();
    }
}
