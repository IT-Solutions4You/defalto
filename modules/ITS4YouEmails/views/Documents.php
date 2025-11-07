<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Documents_View extends Vtiger_Basic_View
{
    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('recordDocuments');

        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function recordDocuments(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $recordId = (int)$request->get('record');
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('RECORDS', ITS4YouEmails_Attachment_Model::getParentRecords($recordId));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'recordDocuments', $request->getModule(), $viewer, $request);

        $viewer->view('RecordDocuments.tpl', $module);
    }
}