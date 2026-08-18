<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_GlobalSearch_List_View extends Settings_Vtiger_Index_View
{
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $moduleRows = GlobalSearch_Configuration_Model::getInstance()->getSettingsRows();

        foreach ($moduleRows as &$moduleRow) {
            $moduleRow['field_blocks'] = $this->getFieldBlocks($moduleRow['fields']);
            unset($moduleRow['fields']);
        }
        unset($moduleRow);

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_ROWS', $moduleRows);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->view('List.tpl', $request->getModule(false));
    }

    /**
     * @param array<Vtiger_Field_Model> $fieldModels
     * @return array<string, array<Vtiger_Field_Model>>
     */
    protected function getFieldBlocks(array $fieldModels): array
    {
        $fieldBlocks = [];

        foreach ($fieldModels as $fieldModel) {
            $block = $fieldModel->get('block');
            $blockLabel = $block->label ?? 'LBL_OTHER_INFORMATION';
            $fieldBlocks[$blockLabel][] = $fieldModel;
        }

        return $fieldBlocks;
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'modules.Settings.GlobalSearch.resources.List',
        ];

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }
}
