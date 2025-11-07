<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_ItemsPopupAjax_View extends InventoryItem_ItemsPopup_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getListViewCount');
        $this->exposeMethod('getRecordsCount');
        $this->exposeMethod('getPageCount');
    }

    /**
     * @inheritDoc
     */
    public function getModule(Vtiger_request $request)
    {
        return $request->get('item_module', 'Products');
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $viewer = $this->getViewer($request);

        $this->initializeListViewContents($request, $viewer);
        $moduleName = 'InventoryItem';
        $viewer->assign('MODULE_NAME', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('ItemsPopupContents.tpl', $moduleName, true);
    }
}