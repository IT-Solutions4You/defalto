<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_Module_Model extends Vtiger_Module_Model
{
    public static $BROWSER_MERGE_TAG = '$custom-viewinbrowser$';

    public static function fixStoredName($values)
    {
        if (!isset($values['storedname']) || empty($values['storedname'])) {
            $values['storedname'] = $values['name'];
        }

        return $values;
    }

    /**
     * Function to get the url for the Create Record view of the module
     * @return string - url
     */
    public function getCreateRecordUrl()
    {
        return '';
    }

    /**
     * @return string - url
     */
    public function getManualUrl()
    {
        return '';
    }

    /**
     * Function to get the Quick Links for the module
     *
     * @param array $linkParams
     *
     * @return array List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        $quickLinks = [
            [
                'linktype'  => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl'   => $this->getDefaultUrl(),
                'linkicon'  => '',
            ],
        ];

        foreach ($quickLinks as $quickLink) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        return $links;
    }

    /**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled()
    {
        return false;
    }

    /*
     * Function to get supported utility actions for a module
     */

    public function getPopupUrl()
    {
        return '';
    }

    function getUtilityActionsNames()
    {
        return [];
    }

    public function getNameFields()
    {
        $nameFieldObject = Vtiger_Cache::get('EntityField', $this->getName());
        $moduleName = $this->getName();

        if ($nameFieldObject && $nameFieldObject->fieldname) {
            $this->nameFields = explode(',', $nameFieldObject->fieldname);
        } else {
            $fieldNames = 'filename';
            $this->nameFields = [$fieldNames];

            $entiyObj = new stdClass();
            $entiyObj->basetable = 'vtiger_pdfmaker';
            $entiyObj->basetableid = 'templateid';
            $entiyObj->fieldname = $fieldNames;
            Vtiger_Cache::set('EntityField', $this->getName(), $entiyObj);
        }

        return $this->nameFields;
    }

    function isStarredEnabled()
    {
        return false;
    }

    function isFilterColumnEnabled()
    {
        return false;
    }

    public function GetListviewData()
    {
        $module = 'PDFMaker';
        $adb = PearDatabase::getInstance();
        $result = $this->GetListviewResult();
        $data = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $templateModule = $row['module'];
            $templateId = $row['templateid'];
            $data[] = [
                'templateid'  => $templateId,
                'description' => $row['description'],
                'module'      => vtranslate($templateModule, $templateModule),
                'filename'    => sprintf('<a href="index.php?module=PDFMaker&view=DetailFree&templateid=%s">%s</a>', $templateId, $templateModule),
                'edit_url'    => 'index.php?module=PDFMaker&view=EditFree&return_view=List&templateid=' . $templateId,
            ];
        }

        return $data;
    }

    //DetailView data

    public function GetListviewResult()
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.* FROM vtiger_pdfmaker 
                LEFT JOIN vtiger_pdfmaker_settings USING(templateid) ORDER BY vtiger_pdfmaker.templateid ASC';

        return $adb->pquery($sql, []);
    }

    public function GetDetailViewData($templateid)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
			FROM vtiger_pdfmaker
                        LEFT JOIN vtiger_pdfmaker_settings USING(templateid)
			WHERE vtiger_pdfmaker.templateid=? AND vtiger_pdfmaker.deleted = ?';

        $result = $adb->pquery($sql, [$templateid, '0']);
        $pdftemplateResult = $adb->fetch_array($result);
        $pdftemplateResult['templateid'] = $templateid;

        return $pdftemplateResult;
    }

    public function GetEditViewData($templateid)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
    			FROM vtiger_pdfmaker
    			LEFT JOIN vtiger_pdfmaker_settings USING(templateid)
    			WHERE vtiger_pdfmaker.templateid=? AND vtiger_pdfmaker.deleted = ?';
        $result = $adb->pquery($sql, [$templateid, '0']);

        return $adb->fetch_array($result);
    }

    public function GetAvailableSettings()
    {
        return [];
    }

    public function GetAvailableLanguages()
    {
        if (!isset($_SESSION['template_languages']) || $_SESSION['template_languages'] == '') {
            $adb = PearDatabase::getInstance();
            $temp_res = $adb->pquery('SELECT label, prefix FROM vtiger_language WHERE active = ?', ['1']);

            while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                $template_languages[$temp_row['prefix']] = $temp_row['label'];
            }

            $_SESSION['template_languages'] = $template_languages;
        } else {
            $template_languages = $_SESSION['template_languages'];
        }

        return $template_languages;
    }

    public function getUrlAttributesString(Vtiger_Request $request, $Add_Attr = [])
    {
        $A = [];

        foreach ($this->UrlAttributes as $attr_type) {
            if (!isset($Add_Attr[$attr_type])) {
                if ($request->has($attr_type) && !$request->isEmpty($attr_type)) {
                    $attr_val = $request->get($attr_type);

                    if (is_array($attr_val)) {
                        $attr_val = json_encode($attr_val);
                    }

                    $A[] = $attr_type . '=' . urlencode($attr_val);
                }
            }
        }

        if (count($Add_Attr) > 0) {
            foreach ($Add_Attr as $attr_type => $req_name) {
                if ($request->has($req_name) && !$request->isEmpty($req_name)) {
                    $attr_val = $request->get($req_name);

                    if (is_array($attr_val)) {
                        $attr_val = json_encode($attr_val);
                    }

                    $A[] = $attr_type . '=' . urlencode($attr_val);
                }
            }
        }

        return implode('&', $A);
    }

    public function getModuleIcon($height = '')
    {
        return sprintf('<i class="fa-solid fa-file-pdf" style="font-size: %s"></i>', $height);
    }
}