<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param array $linkParams
     * @return array List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        $quickLinks = array(
            array(
                'linktype' => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl' => $this->getDefaultUrl(),
                'linkicon' => '',
            ),
        );

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
        return array();
    }

    /**
     * Function to get Settings links
     * @return array
     */
    public function getSettingLinks()
    {
        $settingsLinks = array();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_UPGRADE', $this->getName()),
                'linkurl' => 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1',
                'linkicon' => ''
            );

            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_UNINSTALL', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=Uninstall',
                'linkicon' => ''
            );
        }

        return $settingsLinks;
    }

    public function getNameFields()
    {
        $nameFieldObject = Vtiger_Cache::get('EntityField', $this->getName());
        $moduleName = $this->getName();

        if ($nameFieldObject && $nameFieldObject->fieldname) {
            $this->nameFields = explode(',', $nameFieldObject->fieldname);
        } else {
            $fieldNames = 'filename';
            $this->nameFields = array($fieldNames);


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
        $current_user = Users_Record_Model::getCurrentUserModel();

        $result = $this->GetListviewResult();

        $return_data = array();
        $num_rows = $adb->num_rows($result);

        for ($i = 0; $i < $num_rows; $i++) {
            $currModule = $adb->query_result($result, $i, 'module');
            $templateid = $adb->query_result($result, $i, 'templateid');

            $pdftemplatearray = array();
            $pdftemplatearray['templateid'] = $templateid;
            $pdftemplatearray['description'] = $adb->query_result($result, $i, 'description');
            $pdftemplatearray['module'] = vtranslate($currModule, $currModule);
            $pdftemplatearray['filename'] = "<a href=\"index.php?module=PDFMaker&view=DetailFree&templateid=" . $templateid . "\">" . $pdftemplatearray['module'] . "</a>";
            $pdftemplatearray['edit'] = "<li><a class='dropdown-item' href=\"index.php?module=PDFMaker&view=EditFree&return_view=List&templateid=" . $templateid . "\">" . vtranslate('LBL_EDIT', $module) . "</a></li>";

            $return_data [] = $pdftemplatearray;
        }

        return $return_data;
    }

    //DetailView data

    public function GetListviewResult()
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.* FROM vtiger_pdfmaker 
                LEFT JOIN vtiger_pdfmaker_settings USING(templateid) ORDER BY vtiger_pdfmaker.templateid ASC';

        return $adb->pquery($sql, array());
    }

    public function GetDetailViewData($templateid)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
			FROM vtiger_pdfmaker
                        LEFT JOIN vtiger_pdfmaker_settings USING(templateid)
			WHERE vtiger_pdfmaker.templateid=? AND vtiger_pdfmaker.deleted = ?';

        $result = $adb->pquery($sql, array($templateid, '0'));
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
        $result = $adb->pquery($sql, array($templateid, '0'));

        return $adb->fetch_array($result);
    }

    public function GetAvailableSettings()
    {
        return array();
    }

    public function GetAvailableLanguages()
    {
        if (!isset($_SESSION['template_languages']) || $_SESSION['template_languages'] == '') {
            $adb = PearDatabase::getInstance();
            $temp_res = $adb->pquery('SELECT label, prefix FROM vtiger_language WHERE active = ?', array('1'));

            while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                $template_languages[$temp_row['prefix']] = $temp_row['label'];
            }

            $_SESSION['template_languages'] = $template_languages;
        } else {
            $template_languages = $_SESSION['template_languages'];
        }

        return $template_languages;
    }

    public function getUrlAttributesString(Vtiger_Request $request, $Add_Attr = array())
    {
        $A = array();

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
}