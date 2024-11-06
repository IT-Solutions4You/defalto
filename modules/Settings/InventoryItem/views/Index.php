<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Settings_InventoryItem_Index_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $sourceModule = $request->get('source_module');
        $pickListSupportedModules = Settings_InventoryItem_Module_Model::getPicklistSupportedModules();
        if (empty($sourceModule)) {
            //take the first module as the source module
            $sourceModule = $pickListSupportedModules[0]->name;
        }

        $moduleModel = Settings_Picklist_Module_Model::getInstance($sourceModule);
        $viewer = $this->getViewer($request);
        $qualifiedName = $request->getModule(FALSE);
        $viewer->assign('PICKLIST_MODULES',$pickListSupportedModules);
        $viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
        $viewer->assign('QUALIFIED_NAME',$qualifiedName);

        $viewer->view('Index.tpl',$qualifiedName);
    }
}