<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_GetRelatedBlockColumns_Action extends Vtiger_Action_Controller
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $RelatedBlock = new EMAILMaker_RelatedBlock_Model();
        $sec_module = $request->get('secmodule');
        $pri_module = $request->get('primodule');
        $mode = $request->get('mode');
        $module_list = $RelatedBlock->getModuleList($sec_module);
        $content = "";
        if ($mode == "stdcriteria") {
            $options = $RelatedBlock->getStdCriteriaByModule($sec_module, $module_list, $current_user);
            if (count($options) > 0) {
                foreach ($options as $value => $label) {
                    $content .= "<option value='" . $value . "'>" . $label . "</option>";
                }
            }
        } else {
            foreach ($module_list as $blockid => $optgroup) {
                $options = $RelatedBlock->getColumnsListbyBlock($sec_module, $blockid, $pri_module, $current_user);

                if (count($options) > 0) {
                    $content .= "<optgroup label='" . $optgroup . "'>";

                    foreach ($options as $value => $label) {
                        $content .= "<option value='" . $value . "'>" . $label . "</option>";
                    }
                    $content .= "</optgroup>";
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult($content);
        $response->emit();
    }
}