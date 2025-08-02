<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_ListViewSelect_View extends Vtiger_IndexAjax_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("DETAIL") == false) {
            throw new Exception('LBL_PERMISSION_DENIED');
        }
    }

    public function process(Vtiger_Request $request)
    {
        $options = '';
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $recordIds = $this->getRecordsListFromRequest($request);
        $viewer = $this->getViewer($request);
        global $current_language;
        $adb = PearDatabase::getInstance();
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        if (false == $EMAILMaker->CheckPermissions('DETAIL')) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $_REQUEST['idslist'] = implode(";", $recordIds);
        $request->set('idlist', $_REQUEST['idslist']);
        $current_language = Vtiger_Language_Handler::getLanguage();
        $templates = $EMAILMaker->GetAvailableTemplatesArray($request->get('return_module'), true);
        if (count($templates) > 0) {
            $no_templates_exist = 0;
        } else {
            $no_templates_exist = 1;
        }
        $viewer->assign('CRM_TEMPLATES', $templates);
        $viewer->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);

        $template_output = $language_output = "";

        if ($options != "") {
            $template_output = '
		    <tr>
		  		<td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;">
		  			<select name="use_common_template" id="use_common_template" class="detailedViewTextBox" multiple style="width:90%;" size="5">
		        ' . $options . '
		        </select>
		  		</td>
				</tr>
		  ';
            $templates_select = '<select name="use_common_template" id="use_common_template" class="detailedViewTextBox" multiple style="width:90%;" size="5">
		        ' . $options . '
		        </select>';
            $temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active = ?", ['1']);
            while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                $template_languages[$temp_row["prefix"]] = $temp_row["label"];
            }

            //LANGUAGES BLOCK  
            if (count($template_languages) > 1) {
                $options = "";
                foreach ($template_languages as $prefix => $label) {
                    if ($current_language != $prefix) {
                        $options .= '<option value="' . $prefix . '">' . $label . '</option>';
                    } else {
                        $options .= '<option value="' . $prefix . '" selected="selected">' . $label . '</option>';
                    }
                }

                $language_output = '<tr>
		  		<td class="dvtCellInfo" style="width:100%;">    	
		          <select name="template_language" id="template_language" class="detailedViewTextBox" style="width:90%;" size="1">
		  		    ' . $options . '
		          </select>
		  		</td>
		      </tr>';
                $languages_select = '<select name="template_language" id="template_language" class="detailedViewTextBox" style="width:90%;" size="1">
		  		    ' . $options . '
		          </select>';
            } else {
                foreach ($template_languages as $prefix => $label) {
                    $languages_select .= '<input type="hidden" name="template_language" id="template_language" value="' . $prefix . '"/>';
                }
            }
        } else {
            $template_output = '<tr>
		                		<td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;">
		                		  ' . vtranslate("CRM_TEMPLATES_DONT_EXIST", 'EMAILMaker');
            $template_output .= '</td></tr>';
        }
        $viewer->assign('templates_select', $templates_select);
        $viewer->assign('languages_select', $languages_select);

        $viewer->assign('idslist', $_REQUEST['idslist']);
        $viewer->assign('relmodule', $request->get('return_module'));
        $viewer->view("ListViewSelect.tpl", 'EMAILMaker');
    }

    public function getRecordsListFromRequest(Vtiger_Request $request, $model = false)
    {
        $cvId = $request->get('viewname');
        if ($cvId == "") {
            $cvId = $request->get('cvid');
        }
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (!empty($selectedIds) && count($selectedIds) > 0) {
                return $selectedIds;
            }
        }

        $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
        if ($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');
            if (!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }

            return $customViewModel->getRecordIds($excludedIds);
        }
    }
}