<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_IndexAjax_Action extends Vtiger_Action_Controller
{
    public $cu_language = '';

    function __construct()
    {
        parent::__construct();

        $Methods = ['SaveProductBlock', 'deleteProductBlocks', 'downloadMPDF', 'downloadFile', 'installExtension', 'getModuleFields', 'getPreviewContent'];

        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    function checkPermission(Vtiger_Request $request)
    {
        return;
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function SaveProductBlock(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $adb = PearDatabase::getInstance();
        $tplid = $request->get('tplid');
        $template_name = $request->get('template_name');
        $body = $request->get('body');

        if (isset($tplid) && $tplid != '') {
            $sql = 'UPDATE vtiger_pdfmaker_productbloc_tpl SET name=?, body=? WHERE id=?';
            $adb->pquery($sql, [$template_name, $body, $tplid]);
        } else {
            $sql = 'INSERT INTO vtiger_pdfmaker_productbloc_tpl(name, body) VALUES(?,?)';
            $adb->pquery($sql, [$template_name, $body]);
        }

        header('Location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function deleteProductBlocks(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();

        $sql = 'DELETE FROM vtiger_pdfmaker_productbloc_tpl WHERE id IN (';
        $params = [];

        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) == 'chx_' && $val == 'on') {
                [$dump, $id] = explode('_', $key, 2);

                if (is_numeric($id)) {
                    $sql .= '?,';
                    array_push($params, $id);
                }
            }
        }

        if (count($params) > 0) {
            $sql = rtrim($sql, ',') . ')';
            $adb->pquery($sql, $params);
        }

        header('Location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function downloadMPDF(Vtiger_Request $request)
    {
        $error = $errTbl = '';
        $srcZip = 'http://www.its4you.sk/images/extensions/PDFMaker/src/mpdf.zip';
        $trgZip = 'modules/PDFMaker/resources/mpdf.zip';
        $viewer = $this->getViewer($request);

        if (copy($srcZip, $trgZip)) {
            require_once('vtlib/thirdparty/dUnzip2.inc.php');

            $unzip = new dUnzip2($trgZip);
            $unzip->unzipAll(getcwd() . "/modules/PDFMaker/resources/");

            if ($unzip) {
                $unzip->close();
            }
            if (!is_dir("modules/PDFMaker/resources/mpdf")) {
                $error = vtranslate("UNZIP_ERROR", 'PDFMaker');
                $viewer->assign("STEP", "error");
                $viewer->assign("ERROR_TBL", $errTbl);
            }
        } else {
            $error = vtranslate("DOWNLOAD_ERROR", 'PDFMaker');
        }

        if ($error == "") {
            $result = ['success' => true, 'message' => ''];
        } else {
            $result = ['success' => false, 'message' => $error];
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function downloadFile(Vtiger_Request $request)
    {
        $type = $request->get('type');
        $extid = $request->get('extid');
        $fileext = '';
        $ct = '';

        switch ($type) {
            case 'manual':
                $fileext = 'txt';
                $ct = 'text/plain';
                break;
            case 'download':
                $fileext = 'zip';
                $ct = 'application/zip';
                break;
        }

        $filename = $extid . '.' . $fileext;
        $fullFileName = 'modules/PDFMaker/resources/extensions/' . $filename;

        if (file_exists($fullFileName)) {
            $disk_file_size = filesize($fullFileName);
            $filesize = $disk_file_size + ($disk_file_size % 1024);
            $fileContent = fread(fopen($fullFileName, "r"), $filesize);
            header("Content-type: $ct");
            header("Pragma: public");
            header("Cache-Control: private");
            header("Content-Disposition: attachment; filename=$filename");
            header("Content-Description: PHP Generated Data");
            echo $fileContent;
        } else {
            header('Location: index.php?module=PDFMaker&view=Extensions&parenttab=Settings&download_error=true');
        }
    }

    public function getModuleFields(Vtiger_Request $request)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $current_user->get('language');

        $module = $request->get('formodule');
        $forfieldname = $request->get('forfieldname');

        $SelectModuleFields = [];
        $RelatedModules = [];

        if ($module != '') {
            $PDFMakerFieldsModel = new PDFMaker_Fields_Model();
            $SelectModuleFields = $PDFMakerFieldsModel->getSelectModuleFields($module, $forfieldname);
            $RelatedModules = $PDFMakerFieldsModel->getRelatedModules($module);
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'fields' => $SelectModuleFields, 'related_modules' => $RelatedModules]);
        $response->emit();
    }

    public function getPreviewContent(Vtiger_Request $request)
    {
        $source_module = $request->get('source_module');
        $GeneratePDF = PDFMaker_checkGenerate_Model::getInstance();
        $GeneratePDF->set('source_module', $source_module);

        $generate_type = 'inline';

        if ($request->has('generate_type') && !$request->isEmpty('generate_type')) {
            $generate_type = $request->get('generate_type');
        }

        $GeneratePDF->set('generate_type', $generate_type);
        $GeneratePDF->generate($request);
    }
}