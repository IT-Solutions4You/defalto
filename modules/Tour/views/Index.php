<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Tour_Index_View extends Vtiger_Index_View
{
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('GUIDES', Tour_Base_Guide::getAll());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Index.tpl', $moduleName);
    }
}