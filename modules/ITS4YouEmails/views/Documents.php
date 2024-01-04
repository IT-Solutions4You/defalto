<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_Documents_View extends Vtiger_Basic_View
{
    /**
     * @param Vtiger_Request $request
     * @return true
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request)
    {
        $permissions = $this->requiresPermission($request);
        foreach ($permissions as $permission) {
            if (array_key_exists('module_parameter', $permission)) {
                if ($request->has($permission['module_parameter']) && !empty($request->get($permission['module_parameter']))) {
                    $moduleParameter = $request->get($permission['module_parameter']);
                } elseif ($request->has('record') && !empty($request->get('record'))) {
                    $moduleParameter = getSalesEntityType($request->get('record'));
                }
            } else {
                $moduleParameter = 'module';
            }
            if (array_key_exists('record_parameter', $permission)) {
                $recordParameter = $request->get($permission['record_parameter']);
            } else {
                $recordParameter = '';
            }
            if (!Users_Privileges_Model::isPermitted($moduleParameter, $permission['action'], $recordParameter)) {
                throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function postProcess(Vtiger_Request $request)
    {
    }

    /**
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    /**
     * @param Vtiger_Request $request
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
        $viewer->view('RecordDocuments.tpl', $module);
    }
}