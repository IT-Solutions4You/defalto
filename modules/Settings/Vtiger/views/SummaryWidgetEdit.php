<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_SummaryWidgetEdit_View extends Settings_Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $model = Settings_Vtiger_SummaryWidgets_Model::getInstance((string)$request->get('sourceModule'));
        $linkId = (int)$request->get('linkId');
        $configuration = $linkId
            ? $model->getListWidgetConfiguration($linkId)
            : [
                'linkId' => 0,
                'title' => '',
                'targetModule' => '',
                'filterId' => 0,
                'fields' => [],
                'relationType' => Core_SummaryWidgetList_Model::RELATION_REFERENCE,
                'referenceField' => '',
                'relationId' => 0,
            ];
        $viewer = $this->getViewer($request);
        $viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
        $viewer->assign('TARGET_MODULES', Settings_Vtiger_SummaryWidgets_Model::getSupportedTargetModules());
        $viewer->assign('WIDGET_CONFIGURATION', $configuration);
        $viewer->assign('WIDGET_FIELDS', implode(',', $configuration['fields']));
        $viewer->assign('WIDGET_FIELDS_ORDER', implode(';', $configuration['fields']));
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->view('SummaryWidgetEdit.tpl', $request->getModule(false));
    }
}
