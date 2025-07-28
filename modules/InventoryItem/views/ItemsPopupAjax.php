<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
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
        echo $viewer->view('ItemsPopupContents.tpl', $moduleName, true);
    }
}