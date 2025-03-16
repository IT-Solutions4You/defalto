<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_PopupItemEdit_View extends Vtiger_Footer_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);

        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView'];

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        show($moduleName, $request->getAll());

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('SOURCE_MODULE', $request->get('for_module'));
        $viewer->assign('RECORD', $request->get('record'));
        $viewer->view('PopupItemEdit.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->view('PopupItemEditFooter.tpl', $moduleName);
    }
}